<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PlanetController extends AbstractController
{
    #[Route('/planet', name: 'app_planet')]
    public function index(): Response
    {
        return $this->render('planet/index.html.twig', [
            'controller_name' => 'PlanetController',
        ]);
    }
}
