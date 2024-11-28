<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/utilisateur')]
final class UtilisateurController extends AbstractController
{
    #[Route(name: 'app_utilisateur_index', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }
    #[Route('/signin', name: 'app_utilisateur_signin', methods: ['GET', 'POST'])]
    public function signin(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);
        $utilisateur->setCreatedAt(new \DateTimeImmutable());


        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage du mot de passe avant de sauvegarder
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $utilisateur->getPassword());
            $utilisateur->setPassword($hashedPassword);

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Compte créé avec succès !');

            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('utilisateur/signin.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// Exemple de contrôleur de connexion (si nécessaire)
    #[Route('/log', name: 'app_utilisateur_login', methods: ['GET', 'POST'])]
    public function login22(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // Vérification si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('app_sondage_list');
        }

        // Récupération des erreurs d'authentification
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

      /*  if ($request->isMethod('POST')) {
            $username = $request->request->get('_username');
            $password = $request->request->get('_password');

            if (empty($username) || empty($password)) {
                $this->addFlash('error', 'Tous les champs sont obligatoires.');
            }
        }*/
        return $this->render('utilisateur/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/login', name: 'app_utilisateur_login2', methods: ['GET', 'POST'])]
    public function login(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {

        $error = null;
        $lastUsername = $request->get('email', '');

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            // Vérifier si l'utilisateur existe
            $utilisateur = $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
            if (!$utilisateur) {
                $error = 'Utilisateur inexistant.';
            } elseif (!$passwordHasher->isPasswordValid($utilisateur, $password)) {
                $error = 'Mot de passe incorrect.';
            } else {
                // Connecter l'utilisateur et rediriger
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('utilisateur/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
    #[Route('/logout', name: 'app_utilisateur_logout')]
    public function logout(): void
    {
        // Le logout est géré automatiquement par Symfony, rien à faire ici
        throw new \LogicException('Cette méthode peut être vide.');
    }

    #[Route('/{id}', name: 'app_utilisateur_show', methods: ['GET'])]
    public function show(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_utilisateur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_utilisateur_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($utilisateur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }


}
