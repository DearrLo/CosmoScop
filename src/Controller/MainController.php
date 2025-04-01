<?php

namespace App\Controller;

use App\Repository\ActualityRepository; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ActualityRepository $actualityRepository): Response
    {
        $actualities = $actualityRepository->findAll();

        return $this->render('main/index.html.twig', [
            'actualities' => $actualities, 
        ]);
    }
}
