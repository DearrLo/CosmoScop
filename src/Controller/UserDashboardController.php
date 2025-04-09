<?php

namespace App\Controller;

use App\Form\UpdateEmailType;
use App\Form\ChangePasswordType;
use App\Repository\FavoriteRepository;
use App\Repository\UserRepository;
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
    // Tableau de bord de l'user connecté
    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    public function index(FavoriteRepository $favoriteRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER'); // interdit l'accès si la personne n'est pas connectée

        $user = $this->getUser(); // récup l'user en cours
        $favorites = $favoriteRepository->findBy(['user' => $user]); // on récupère ses favoris

        return $this->render('user_dashboard/index.html.twig', [
            'user' => $user,
            'favorites' => $favorites,
        ]);
    }

    // Formulaire pour changer l'email
    #[Route('/user/change-email', name: 'app_user_change_email')]
    public function changeEmail(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $form = $this->createForm(UpdateEmailType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush(); // on enregistre le nouvel email
            $this->addFlash('success', 'Mail updated.');
            return $this->redirectToRoute('app_user_dashboard');
        }

        return $this->render('user_dashboard/change_email.html.twig', [
            'emailForm' => $form->createView(),
        ]);
    }

    // Formulaire pour changer le mdp
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

            // on vérifie que l'ancien mot de passe est correct
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $form->get('currentPassword')->addError(new FormError('This is not the right password. Please try again.'));
            } else {
                // on encode le nouveau mot de passe
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

    // Upload/modification de la photo de profil en .webp ONLY
    #[Route('/user/upload-picture', name: 'app_user_upload_picture', methods: ['POST'])]
    public function uploadPicture(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        $file = $request->files->get('profile_picture');

        if ($file) {

            // On vérifie que le fichier est bien une image autorisée
            if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
                $this->addFlash('error', 'Only JPEG, PNG, or WebP images are allowed.');
                return $this->redirectToRoute('app_user_dashboard');
            }

            // On crée un nom de fichier unique en .webp
            $filename = 'user_' . $user->getId() . '.webp';
            $outputPath = $this->getParameter('profile_pictures_directory') . '/' . $filename;

            // On crée une image temporaire en fonction du type original
            $image = null;
            switch ($file->getMimeType()) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($file->getPathname());
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($file->getPathname());
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($file->getPathname());
                    break;
            }

            // On convertit tout ça en .webp et on l’enregistre
            if ($image && imagewebp($image, $outputPath, 80)) {
                imagedestroy($image);

                $user->setPicture($filename);
                $em->flush();

            } else {
            }
        } else {
            $this->addFlash('error', 'No file selected.');
        }

        return $this->redirectToRoute('app_user_dashboard');
    }

    // Vue admin pour afficher les infos d’un utilisateur spécifique
    #[Route('/admin/user/{id}', name: 'admin_user_dashboard')]
    public function adminView(UserRepository $userRepository, FavoriteRepository $favoriteRepository, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        $favorites = $favoriteRepository->findBy(['user' => $user]);

        return $this->render('user_dashboard/index.html.twig', [
            'user' => $user,
            'favorites' => $favorites,
        ]);
    }
}
