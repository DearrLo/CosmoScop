<?php

namespace App\Controller;

use App\Entity\Actuality;
use App\Entity\Favorite;
use App\Form\ActualityType;
use App\Repository\ActualityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/actuality')]
final class ActualityController extends AbstractController
{
    #[Route('/', name: 'app_actuality_index', methods: ['GET'])]
    public function index(ActualityRepository $actualityRepository): Response
    {
        return $this->render('actuality/index.html.twig', [
            'actualities' => $actualityRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_actuality_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'app_actuality_edit', methods: ['GET', 'POST'])]
    public function form(Request $request, EntityManagerInterface $entityManager, Actuality $actuality = null): Response
    {
        $actuality = $actuality ?? new Actuality();
        $form = $this->createForm(ActualityType::class, $actuality);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($actuality);
            $entityManager->flush();

            return $this->redirectToRoute('app_actuality_index');
        }

        return $this->render('actuality/form.html.twig', [
            'form' => $form->createView(),
            'actuality' => $actuality,
        ]);
    }

    #[Route('/{id}', name: 'app_actuality_show', methods: ['GET'])]
    public function show(Actuality $actuality, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $actualityIsFavorite = false;

        if ($user) {
            $favorite = $em->getRepository(Favorite::class)->findOneBy([
                'user' => $user,
                'actuality' => $actuality,
            ]);
            $actualityIsFavorite = $favorite !== null;
        }

        return $this->render('actuality/show.html.twig', [
            'actuality' => $actuality,
            'actualityIsFavorite' => $actualityIsFavorite,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_actuality_delete', methods: ['POST'])]
    public function delete(Request $request, Actuality $actuality, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $actuality->getId(), $request->request->get('_token'))) {
            $entityManager->remove($actuality);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_actuality_index');
    }
}
