<?php

namespace App\Controller;

use App\Entity\Signalement;
use App\Form\SignalementType;
use App\Repository\SignalementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/signalement')]
final class SignalementController extends AbstractController
{
    #[Route(name: 'app_signalement_index', methods: ['GET'])]
    public function index(SignalementRepository $signalementRepository): Response
    {
        return $this->render('signalement/index.html.twig', [
            'signalements' => $signalementRepository->findAll(),
        ]);
    }

    #[Route('/submit-signalement}', name: 'submit_signalement', methods: ['POST'])]
    public function submitSignalement(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer l'utilisateur signalé
        $id = $request->request->get('reported_user_id');
        if (empty($id)) {
            throw $this->createNotFoundException('User ID is required');
        }
        $userSignaler = $userRepository->find($id);
        if (!$userSignaler) {
            throw $this->createNotFoundException('User not found');
        }

        // Récupérer l'utilisateur connecté
        $userSignaleur = $this->getUser();

        // Créer un nouveau signalement
        $signalement = new Signalement();
        $signalement->setUserSignaler($userSignaler);
        $signalement->setUserSignaleur($userSignaleur);

        $raison = $request->request->get('raison');
        if (empty($raison)) {
            throw $this->createNotFoundException('Raison is required');
        }

        $signalement->setRaison($request->request->get('raison'));

        // Sauvegarder le signalement dans la base de données
        $entityManager->persist($signalement);
        $entityManager->flush();

        // Ajouter un message flash ou autre traitement si nécessaire
        $this->addFlash('success', 'Utilisateur signalé avec succès');

        // Rediriger vers la page précédente avec une query string
        $referer = $request->headers->get('referer');
        if ($referer) {
            $urlWithSuccess = $referer . (parse_url($referer, PHP_URL_QUERY) ? '&' : '?') . 'success=Utilisateur signalé avec succès';
            return $this->redirect($urlWithSuccess);
        }

        // Si aucun referer n'est présent, rediriger vers une page par défaut
        return $this->redirectToRoute('app_signalement_index', ['success' => 'Utilisateur signalé avec succès']);
    }

    #[Route('/new', name: 'app_signalement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $signalement = new Signalement();
        $form = $this->createForm(SignalementType::class, $signalement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($signalement);
            $entityManager->flush();

            return $this->redirectToRoute('app_signalement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('signalement/new.html.twig', [
            'signalement' => $signalement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_signalement_show', methods: ['GET'])]
    public function show(Signalement $signalement): Response
    {
        return $this->render('signalement/show.html.twig', [
            'signalement' => $signalement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_signalement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Signalement $signalement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SignalementType::class, $signalement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_signalement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('signalement/edit.html.twig', [
            'signalement' => $signalement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_signalement_delete', methods: ['POST'])]
    public function delete(Request $request, Signalement $signalement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$signalement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($signalement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_signalement_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/signalement/ban', name: 'app_signalement_ban', methods: ['POST'])]
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

        // Rediriger vers la liste des signalements
        return $this->redirectToRoute('app_signalement_index');
    }


}
