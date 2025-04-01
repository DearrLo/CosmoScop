<?php
namespace App\Controller;

use App\Repository\FutureMissionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FutureMissionController extends AbstractController
{
    #[Route('/future/mission', name: 'app_future_mission')]
    public function index(FutureMissionRepository $futureMissionRepository): Response
    {
        $missions = $futureMissionRepository->findAll();

        return $this->render('future_mission/index.html.twig', [
            'missions' => $missions, 
        ]);
    }
}
