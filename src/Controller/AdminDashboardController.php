<?php
namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\ActualityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Ce contrôleur gère l'affichage du tableau de bord côté admin
final class AdminDashboardController extends AbstractController
{
    // Quand on va sur /admin/dashboard, ça lance cette fonction
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(
        UserRepository $userRepo,
        ArticleRepository $articleRepo,
        ActualityRepository $actualityRepo
    ): Response {
        
        // On récupère tous les membres enregistrés
        $members = $userRepo->findAll();

        // Pareil pour les articles
        $articles = $articleRepo->findAll();

        // Et pour les actualités
        $actualities = $actualityRepo->findAll();

        // Ensuite on envoie tout ça à la vue Twig pour l'affichage
        return $this->render('admin_dashboard/index.html.twig', [
            'controller_name' => 'AdminDashboardController',
            'members' => $members,
            'articles' => $articles,
            'actualities' => $actualities,
        ]);
    }
}
