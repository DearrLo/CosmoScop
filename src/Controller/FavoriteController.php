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
    #[Route('/', name: 'app_favorite_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(FavoriteRepository $favoriteRepository): Response
    {
        $user = $this->getUser();
        $favorites = $favoriteRepository->findBy(['user' => $user]);

        return $this->render('favorite/index.html.twig', [
            'favorites' => $favorites,
        ]);
    }

    #[Route('/toggle/{id}', name: 'app_favorite_toggle', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggle(Actuality $actuality, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $repository = $entityManager->getRepository(Favorite::class);
        $existingFavorite = $repository->findOneBy([
            'user' => $user,
            'actuality' => $actuality,
        ]);

        if ($existingFavorite) {
            $entityManager->remove($existingFavorite);
        } else {
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setActuality($actuality);
            $entityManager->persist($favorite);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_actuality_show', [
            'id' => $actuality->getId(),
        ]);
    }
}
