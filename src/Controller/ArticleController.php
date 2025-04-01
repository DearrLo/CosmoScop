<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/article')]
final class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function form(
        Request $request,
        EntityManagerInterface $entityManager,
        Article $article = null
    ): Response {
        if (!$article) {
            throw $this->createNotFoundException('No article found to edit.');
        }

        $form = $this->createForm(ArticleType::class, $article)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );

                $article->setImageFilename($newFilename);
            }

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/topic/{topic}', name: 'app_article_topic', methods: ['GET'], requirements: ['topic' => 'introduction|key-moments|pioneers|economic-benefits'])]
    public function topic(string $topic, ArticleRepository $articleRepository): Response
    {
        $titles = [
            'introduction'        => 'Introduction',
            'key-moments'         => 'Key Moments',
            'pioneers'            => 'Pioneers',
            'economic-benefits'   => 'Economic & Industrial Benefits',
        ];

        if (!isset($titles[$topic])) {
            throw $this->createNotFoundException('Sujet invalide.');
        }

        $article = $articleRepository->findOneBy(['title' => $titles[$topic]]);
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé pour le sujet: ' . $topic);
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }
}
