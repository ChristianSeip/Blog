<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Find posts with keyword in title and/or content
     *
     * @param string $query
     * @return Post[]
     */
    public function searchByTitleAndContent(string $query): array
    {
        $qb = $this->createQueryBuilder('b');
        return $qb
            ->where($qb->expr()->orX(
                $qb->expr()->like('b.title', ':query'),
                $qb->expr()->like('b.content', ':query')
            ))
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find latest posts.
     *
     * @param int $limit
     * @return array
     */
    public function findLatest(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find random posts.
     *
     * @param int $limit
     * @return array
     */
    public function findRandom(int $limit): array
    {
        $qb = $this->createQueryBuilder('p');
        $count = (int) $qb->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $offset = max(0, rand(0, $count - $limit));
        return $qb->select('p')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }
}
