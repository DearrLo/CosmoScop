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
    // Cette route gère la page d’inscription (GET pour afficher le formulaire, POST pour le traiter)
    #[Route('/registration', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager
    ): Response {

        // On crée un nouvel utilisateur vide (qu'on va remplir via form)
        $user = new User();

        // On génère le formulaire lié à l’objet User
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request); // On gère la soumission du formulaire

        // Si le formulaire est envoyé ET qu’il est valide alors :
        if ($form->isSubmitted() && $form->isValid()) {

            // On hash le mot de passe en récupérant ce que l’utilisateur a tapé dans le champ "plainPassword"
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            // On définit le rôle de base (ici, juste un basic user)
            $user->setRole('ROLE_USER');

            // On enregistre le nouvel utilisateur en bdd
            $entityManager->persist($user);
            $entityManager->flush();

            // Petit message flash pour dire que l’inscription a marché
            $this->addFlash('success', 'Account created successfully');

            // On redirige l’utilisateur vers la page d’accueil
            return $this->redirectToRoute('home');
        }

        // Si le formulaire n’est pas soumis ou pas valide, on l’affiche
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
