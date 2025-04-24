<?php
namespace App\Controller;

use App\Entity\Actuality;
use App\Entity\Comment;
use App\Entity\Favorite;
use App\Form\ActualityType;
use App\Form\CommentType;
use App\Repository\ActualityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

// Ce controller gère tout ce qui touche aux acutality 
#[Route('/actuality')]
final class ActualityController extends AbstractController
{
    // Page d'accueil des actualités; on récupère tout et on les affiche dans une vue
    #[Route('/', name: 'app_actuality_index', methods: ['GET'])]
    public function index(ActualityRepository $actualityRepository): Response
    {
        return $this->render('actuality/index.html.twig', [
            'actualities' => $actualityRepository->findAll(),
        ]);
    }

    // Page alternative d'affichage des actualités (liste personnalisée)
    #[Route('/list', name: 'app_actuality_list', methods: ['GET'])]
    public function list(ActualityRepository $actualityRepository): Response
    {
        return $this->render('actuality/list.html.twig', [
            'actualities' => $actualityRepository->findAll(),
        ]);
    }

    // Ce form() sert à créer ou edit une actu, selon si on reçoit un objet Actuality ou non
    #[Route('/new', name: 'app_actuality_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'app_actuality_edit', methods: ['GET', 'POST'])]
    public function form(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, Actuality $actuality = null): Response
    {
        // On vérifie que seul un admin peut accéder à cette page
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Si pas d'actu en paramètre, c'est qu'on en crée une nouvelle
        $actuality = $actuality ?? new Actuality();
        
        // On crée le formulaire lié à l'actu
        $form = $this->createForm(ActualityType::class, $actuality);
        $form->handleRequest($request);

        // Si tout est bon (form soumis + valide), on enregistre l'actu en base
        if ($form->isSubmitted() && $form->isValid()) {

            // On gère l'image s’il y en a une
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('actuality_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // En cas d'erreur pendant l'upload, on peut logguer ou ignorer
                }

                $actuality->setImageFilename($newFilename);
            }

            $entityManager->persist($actuality);
            $entityManager->flush();

            // Ensuite on redirige vers l'admin dashboard
            return $this->redirectToRoute('app_admin_dashboard');
        }

        // SINON, on affiche juste le formulaire (création ou édition)
        return $this->render('actuality/form.html.twig', [
            'form' => $form->createView(),
            'actuality' => $actuality,
        ]);
    }

    // Affiche une actu en détail + on vérifie si elle est en favoris pour l'utilisateur connecté
    #[Route('/{id}', name: 'app_actuality_show', methods: ['GET', 'POST'])]
    public function show(Actuality $actuality, EntityManagerInterface $em, Request $request, Security $security): Response
    {
        $user = $this->getUser(); // on récupère l'utilisateur connecté

        $actualityIsFavorite = false;

        if ($user) {

            // On cherche si cette actu est déjà dans ses favoris
            $favorite = $em->getRepository(Favorite::class)->findOneBy([
                'user' => $user,
                'actuality' => $actuality,
            ]);
            $actualityIsFavorite = $favorite !== null;
        }

        // Traitement du formulaire de commentaire
        $comment = new Comment();
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setUser($user);
        $comment->setActuality($actuality);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('app_actuality_show', [
                'id' => $actuality->getId(),
            ]);
        }

        // On envoie les infos à la vue (l'actu + si c’est un favori ou pas)
        return $this->render('actuality/show.html.twig', [
            'actuality' => $actuality,
            'actualityIsFavorite' => $actualityIsFavorite,
            'form' => $form->createView(),
        ]);
    }

    // Supprime une actu (si le token CSRF est valide, donc pas de suppression à l’arrache)
    #[Route('/{id}/delete', name: 'app_actuality_delete', methods: ['POST'])]
    public function delete(Request $request, Actuality $actuality, EntityManagerInterface $entityManager): Response
    {
        // Seuls les admins peuvent supprimer
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete' . $actuality->getId(), $request->request->get('_token'))) {
            $entityManager->remove($actuality);
            $entityManager->flush();
        }

        // Après suppression (ou si CSRF pas bon), on retourne sur la liste
        return $this->redirectToRoute('app_admin_dashboard');
    }
}
