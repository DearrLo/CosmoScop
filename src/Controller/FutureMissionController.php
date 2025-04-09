<?php

namespace App\Controller;

use App\Entity\FutureMission;
use App\Form\FutureMissionType;
use App\Repository\FutureMissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/future_mission')]
final class FutureMissionController extends AbstractController
{
    // Affiche la liste de toutes les missions 
    #[Route('/', name: 'app_future_mission', methods: ['GET'])]
    public function index(FutureMissionRepository $futureMissionRepository): Response
    {
        // Récupération de toutes les missions depuis la BDD
        $missions = $futureMissionRepository->findAll();

        // Affichage dans le template associé
        return $this->render('future_mission/index.html.twig', [
            'missions' => $missions,
        ]);
    }

    // Affiche les détails d’une mission en passant par son ID (ex : /future_mission/2)
    #[Route('/{id}', name: 'app_future_mission_show', methods: ['GET'])]
    public function show(FutureMission $mission): Response
    {
        return $this->render('future_mission/show.html.twig', [
            'mission' => $mission,
        ]);
    }

    // Édition d'une mission existante
    #[Route('/{id}/edit', name: 'app_future_mission_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FutureMission $mission, EntityManagerInterface $em): Response
    {
        // Création du formulaire à partir de l’entité FutureMission
        $form = $this->createForm(FutureMissionType::class, $mission);
        $form->handleRequest($request);

        // Traitement si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            // Si une image est uploadée, on la sauvegarde avec un nom unique
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );

                // On met à jour l’entité avec le nom du fichier
                $mission->setImageFilename($newFilename);
            }

            // Save en bdd
            $em->flush();

            // Redirection vers la page de détails de la mission
            return $this->redirectToRoute('app_future_mission_show', ['id' => $mission->getId()]);
        }

        // Si le formulaire est vide ou invalide, on l'affiche
        return $this->render('future_mission/edit.html.twig', [
            'form' => $form->createView(),
            'mission' => $mission,
        ]);
    }

    // Route personnalisée qui affiche une mission selon un "slug" lisible (ex: /mission/artemis-BLABLA)
    #[Route('/mission/{slug}', name: 'app_future_mission_slug', methods: ['GET'])]
    public function bySlug(string $slug, FutureMissionRepository $repo): Response
    {
        // Table de correspondance entre slug (URL) et titres exacts en BDD
        $titles = [
            'artemis-iii'         => 'Artemis III',
            'juice'              => 'JUICE (Jupiter Icy Moons Explorer)',
            'change-6'           => 'Chang’e 6',
            'mars-sample-return' => 'Mars Sample Return',
        ];

        // Si le slug ne correspond à aucune entrée connue, on affiche une 404
        if (!isset($titles[$slug])) {
            throw $this->createNotFoundException('Mission inconnue.');
        }

        // On cherche en BDD la mission qui a ce titre précis
        $mission = $repo->findOneBy(['title' => $titles[$slug]]);
        if (!$mission) {
            throw $this->createNotFoundException('Mission non trouvée.');
        }

        // Et on affiche la mission comme dans la route show classique
        return $this->render('future_mission/show.html.twig', [
            'mission' => $mission,
        ]);
    }
}
