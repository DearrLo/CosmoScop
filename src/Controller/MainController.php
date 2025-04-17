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
        // On récupère toutes les actu depuis la bdd
        $actualities = $actualityRepository->findAll();
    
        // On met tout ça dans la vue d’accueil
        return $this->render('main/index.html.twig', [
            'actualities' => $actualities, 
        ]);
    }
}    