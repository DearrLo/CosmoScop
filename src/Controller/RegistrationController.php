<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_register', methods: ['GET', 'POST'])]

    // Fonction qui gère l'inscription. Reply qui retourne la rép HTTP
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager
    ): Response {
        // Crée un nouvel utilisateur
        $user = new User();

        // Crée le formulaire lié à l'entité User
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Si le formulaire est valide ET sans erreur, on continue
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            // Assigne le rôle par défaut ROLE_USER 
            $user->setRole('ROLE_USER');

            // Sauvegarde l'utilisateur en bdd
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirection vers home page après inscription réussie
            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
