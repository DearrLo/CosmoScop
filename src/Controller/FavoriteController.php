<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Entity\Actuality;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

#[Route('/favorite')]
class FavoriteController extends AbstractController
{
    // Affiche tous les favoris de l'user connecté
    #[Route('/', name: 'app_favorite_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] // On bloque l'accès à ceux qui sont pas connectés
    public function index(FavoriteRepository $favoriteRepository): Response
    {
        // On récupère l'user connecté
        $user = $this->getUser();

        // On va chercher tous ses favoris dans la base
        $favorites = $favoriteRepository->findBy(['user' => $user]);

        // Et on les envoie à la vue Twig pour les afficher
        return $this->render('favorite/index.html.twig', [
            'favorites' => $favorites,
        ]);
    }

    // Ici ça permet d'ajouter ou retirer une actu des favoris 
    #[Route('/toggle/{id}', name: 'app_favorite_toggle', methods: ['POST'])]

    #[IsGranted('ROLE_USER')] // Faut être connecté pour pouvoir ajouter/retirer des favoris
    public function toggle(Actuality $actuality, EntityManagerInterface $entityManager): Response
    {
        // On attrape l'utilisateur actuel
        $user = $this->getUser();

        // On cherche si cette actu est déjà en favori
        $repository = $entityManager->getRepository(Favorite::class);
        $existingFavorite = $repository->findOneBy([
            'user' => $user,
            'actuality' => $actuality,
        ]);

        // Si oui, on la retire
        if ($existingFavorite) {
            $entityManager->remove($existingFavorite);
        } else {
            // Sinon, on crée un nouveau favori et on l’ajoute
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setActuality($actuality);
            $entityManager->persist($favorite);
        }

        // On enregistre en base
        $entityManager->flush();

        // Et on revient sur la page de l’actu
        return $this->redirectToRoute('app_actuality_show', [
            'id' => $actuality->getId(),
        ]);
    }
}
