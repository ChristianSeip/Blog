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
}
