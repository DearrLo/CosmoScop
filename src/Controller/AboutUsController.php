<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutUsController extends AbstractController
{

    //  La route pour le abouts-us
    #[Route('/about-us', name: 'about_us')]
    public function index(): Response
    {
        return $this->render('about_us/index.html.twig');
    }
}
