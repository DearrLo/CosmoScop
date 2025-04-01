<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\ActualityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminDashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(
        UserRepository $userRepo,
        ArticleRepository $articleRepo,
        ActualityRepository $actualityRepo
    ): Response {
        $members = $userRepo->findAll();
        $articles = $articleRepo->findAll();
        $actualities = $actualityRepo->findAll();

        return $this->render('admin_dashboard/index.html.twig', [
            'controller_name' => 'AdminDashboardController',
            'members' => $members,
            'articles' => $articles,
            'actualities' => $actualities,
        ]);
    }
}

