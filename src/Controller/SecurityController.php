<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    // Route qui gère la page de connexion
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // On récupère la dernière erreur de connexion (si jamais)
        $error = $authenticationUtils->getLastAuthenticationError();

        // préremplir l’email après une erreur de connexion (ce qui améliore le confort user)
        $lastUsername = $authenticationUtils->getLastUsername();

        // On envoie le tout à la vue Twig pour afficher le formulaire
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // Route de déconnexion
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony s’occupe de la déconnexion via le firewall (zone de sécu pour l'authentif, droits selon page des user/admins)
        throw new \LogicException('This method can be blank - Symfony gère cela automatiquement.');
    }
}
