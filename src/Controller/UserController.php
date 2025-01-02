<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\SignalementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, SignalementRepository $signalementRepository): Response
    {
        // Récupérer tous les utilisateurs
        $users = $userRepository->findAll();

        // Calculer le nombre de signalements reçus pour chaque utilisateur
        $userSignalements = [];
        foreach ($users as $user) {
            // Trouver le nombre de signalements où cet utilisateur est la personne signalée
            $signalementCount = $signalementRepository->count(['userSignaler' => $user]);
            $userSignalements[$user->getId()] = $signalementCount;
        }
        return $this->render('user/index.html.twig', [
            'users' => $users,
            'userSignalements' => $userSignalements,
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image de profil
            /** @var UploadedFile|null $profilePic */
            $profilePic = $form->get('profilePic')->getData();
            if ($profilePic) {
                // Définir un chemin d'enregistrement (ajustez selon vos besoins)
                $uploadsDirectory = $this->getParameter('uploads_directory'); // Assurez-vous que ce paramètre est défini dans config/services.yaml
                $newFilename = uniqid() . '.' . $profilePic->guessExtension();

                // Déplacez le fichier vers le dossier d'uploads
                $profilePic->move($uploadsDirectory, $newFilename);

                // Mettre à jour le chemin de l'image dans l'entité utilisateur
                $user->setProfilePic($newFilename);
            }

            // Gestion du mot de passe
            $newPassword = $form->get('plainPassword')->getData();
            if ($newPassword) {
                $encodedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($encodedPassword);
            }

            // Enregistrer les modifications
            $entityManager->flush();

            return $this->redirectToRoute('app_profile_index', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
// src/Controller/UserController.php

    #[Route('/user/ban', name: 'app_user_ban', methods: ['POST'])]
    public function ban(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $userId = $request->request->get('userId');
        $dateFinBan = $request->request->get('dateFinBan');

        // Vérifier si l'utilisateur existe
        $user = $userRepository->find($userId);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Mettre à jour la date de fin de bannissement
        $user->setDateFinBan(new \DateTime($dateFinBan));

        // Sauvegarder les changements dans la base de données
        $entityManager->flush();

        // Rediriger vers la liste des utilisateurs
        return $this->redirectToRoute('app_user_index');
    }


}
