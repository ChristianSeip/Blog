<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\CreatePostFormType;
use App\Repository\PostRepository;
use App\Service\BBCodeParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller managing CRUD operations for posts.
 */
class PostController extends AbstractController
{
    /**
     * Lists all posts.
     *
     * @param EntityManagerInterface $entityManager
     * @param BBCodeParser $bbcodeParser
     * @return Response
     */
    #[Route('/posts', name: 'app_posts')]
    public function list(EntityManagerInterface $entityManager,BBCodeParser $bbcodeParser): Response
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();
        foreach ($posts as $post) {
            $post->setContent($bbcodeParser->parse($post->getContent()));
        }
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'latestPosts' => $entityManager->getRepository(Post::class)->findLatest(3),
            'randomPosts' => $entityManager->getRepository(Post::class)->findRandom(3)
        ]);
    }

    /**
     * Displays a specific post.
     *
     * @param string $id
     * @param EntityManagerInterface $entityManager
     * @param BBCodeParser $bbcodeParser
     * @return Response
     */
    #[Route('/post/show/{id}', name: 'app_post_show')]
    public function show(string $id, EntityManagerInterface $entityManager, BBCodeParser $bbcodeParser): Response
    {
        $post = $entityManager->getRepository(Post::class)->findOneBy(['id' => $id]);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }
        $post->setContent($bbcodeParser->parse($post->getContent()));
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'latestPosts' => $entityManager->getRepository(Post::class)->findLatest(3),
            'randomPosts' => $entityManager->getRepository(Post::class)->findRandom(3)
        ]);
    }

    /**
     * Creates a new post.
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/post/new', name: 'app_post_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(CreatePostFormType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success', 'Post created successfully.');
            return $this->redirectToRoute('app_posts');
        }
        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete the given post.
     *
     * @param Post $post
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/post/delete/{id}', name: 'app_post_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Post $post, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($post);
        $entityManager->flush();
        $this->addFlash('success', 'Post deleted successfully.');
        return $this->redirectToRoute('app_posts');
    }

    /**
     * Searches for posts by title and content.
     *
     * @param PostRepository $postRepository
     * @param Request $request
     * @param BBCodeParser $bbcodeParser
     * @return Response
     */
    #[Route('/posts/search', name: 'app_post_search', methods: ['GET'])]
    public function searchBlogPost(PostRepository $postRepository, Request $request, BBCodeParser $bbcodeParser): Response
    {
        $query = $request->query->get('keyword');
        $posts = [];
        if ($query) {
            $posts = $postRepository->searchByTitleAndContent($query);
            foreach ($posts as $post) {
                $post->setContent($bbcodeParser->parse($post->getContent()));
            }
        }
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'latestPosts' => $postRepository->findLatest(3),
            'randomPosts' => $postRepository->findRandom(3)
        ]);
    }
}
