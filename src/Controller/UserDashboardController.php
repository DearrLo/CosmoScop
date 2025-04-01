<?php

namespace App\Controller;

use App\Form\UpdateEmailType;
use App\Form\ChangePasswordType;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Attribute\AsController;

#[AsController]
class UserDashboardController extends AbstractController
{
    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    public function index(FavoriteRepository $favoriteRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $favorites = $favoriteRepository->findBy(['user' => $user]);

        return $this->render('user_dashboard/index.html.twig', [
            'user' => $user,
            'favorites' => $favorites,
        ]);
    }

    #[Route('/user/change-email', name: 'app_user_change_email')]
    public function changeEmail(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $form = $this->createForm(UpdateEmailType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Mail updated.');
            return $this->redirectToRoute('app_user_dashboard');
        }

        return $this->render('user_dashboard/change_email.html.twig', [
            'emailForm' => $form->createView(),
        ]);
    }

    #[Route('/user/change-password', name: 'app_user_change_password')]
    public function changePassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $form->get('currentPassword')->addError(new FormError('This is not the right password. Please try again.'));
            } else {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
                $em->flush();
                $this->addFlash('success', 'Password successfully updated.');
                return $this->redirectToRoute('app_user_dashboard');
            }
        }

        return $this->render('user_dashboard/change_password.html.twig', [
            'passwordForm' => $form->createView(),
        ]);
    }

    #[Route('/user/upload-picture', name: 'app_user_upload_picture', methods: ['POST'])]
    public function uploadPicture(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $file = $request->files->get('profile_picture');

        if ($file) {
            if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
                $this->addFlash('error', 'File not authorized. Only JPEG, PNG or WEBP formats are allowed.');
                return $this->redirectToRoute('app_user_dashboard');
            }

            $filename = 'user_' . $user->getId() . '.' . $file->guessExtension();
            $file->move($this->getParameter('profile_pictures_directory'), $filename);

            $user->setPicture($filename);
            $em->flush();

            $this->addFlash('success', 'Profile picture updated.');
        } else {
            $this->addFlash('error', 'Error. No file selected.');
        }

        return $this->redirectToRoute('app_user_dashboard');
    }
}
