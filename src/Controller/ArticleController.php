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
    // Route qui affiche tous les articles (index des articles)
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        // On récupère tous les articles depuis la base
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    // Route pour modifier un article existant
    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function form(
        Request $request,
        EntityManagerInterface $entityManager,
        Article $article = null
    ): Response {

        // Si on reçoit pas d'article, on renvoie une erreur
        if (!$article) {
            throw $this->createNotFoundException('No article found to edit.');
        }

        // On crée le formulaire avec les données de l’article et on traite la requête
        $form = $this->createForm(ArticleType::class, $article)->handleRequest($request);

        // Si le formulaire est envoyé ET valide
        if ($form->isSubmitted() && $form->isValid()) {
            
            // On récupère le fichier image uploadé (s’il y en a un)
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {

                // On génère un nouveau nom de fichier unique avec l'extension
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                // On déplace l’image dans le dossier de stockage défini
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );

                // On met à jour l’article avec le nom du nouveau fichier image
                $article->setImageFilename($newFilename);
            }

            // On sauvegarde les modifs en base
            $entityManager->persist($article);
            $entityManager->flush();

            // Et on redirige vers la page de l’article
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        // SI le formulaire est pas encore soumis ou pas valide, on l’affiche
        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    // Route pour afficher un article en détail
    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    // Route qui affiche un article en fonction d’un sujet précis (genre introduction, pionniers… depuis où on clic dans le dropdown)
    #[Route('/topic/{topic}', name: 'app_article_topic', methods: ['GET'], requirements: ['topic' => 'introduction|key-moments|pioneers|economic-benefits'])]
    public function topic(string $topic, ArticleRepository $articleRepository): Response
    {
        // Liste des sujets autorisés avec leur titre exact dans la BDD
        $titles = [
            'introduction'        => 'Introduction',
            'key-moments'         => 'Key Moments',
            'pioneers'            => 'Pioneers',
            'economic-benefits'   => 'Economic & Industrial Benefits',
        ];

        // Si le topic demandé n’est pas dans la liste, on renvoie une erreur
        if (!isset($titles[$topic])) {
            throw $this->createNotFoundException('Sujet invalide.');
        }

        // On cherche l’article qui correspond exactement au titre du sujet
        $article = $articleRepository->findOneBy(['title' => $titles[$topic]]);
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé pour le sujet: ' . $topic);
        }

        // Et on affiche la page de l’article
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }
}
