<?php

namespace App\Controller;

use App\Entity\Choix;
use App\Entity\Utilisateur;
use App\Entity\Vote;
use App\Form\VoteType;
use App\Repository\SondageRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vote')]
final class VoteController extends AbstractController
{
    #[Route(name: 'app_vote_index', methods: ['GET'])]
    public function index(VoteRepository $voteRepository): Response
    {
        return $this->render('vote/index.html.twig', [
            'votes' => $voteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_vote_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vote = new Vote();
        $form = $this->createForm(VoteType::class, $vote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vote->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($vote);
            $entityManager->flush();

            return $this->redirectToRoute('app_vote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vote/new.html.twig', [
            'vote' => $vote,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vote_show', methods: ['GET'])]
    public function show(Vote $vote): Response
    {
        return $this->render('vote/show.html.twig', [
            'vote' => $vote,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vote_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vote $vote, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VoteType::class, $vote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_vote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vote/edit.html.twig', [
            'vote' => $vote,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vote_delete', methods: ['POST'])]
    public function delete(Request $request, Vote $vote, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vote->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($vote);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_vote_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/submit-vote/{id}', name: 'submit_vote', methods: ['POST'])]
    public function submitVote(int $id, Request $request, SondageRepository $sondageRepository, EntityManagerInterface $entityManager): Response {
        // Récupérer le sondage
        $sondage = $sondageRepository->find($id);
        if (!$sondage) {
            throw $this->createNotFoundException('Sondage introuvable.');
        }

        $userId = $request->request->get('user_id');
        $user = $entityManager->getRepository(Utilisateur::class)->find($userId);

        // Récupérer les choix sélectionnés
        $selectedChoices = $request->request->all('choices');
        if (empty($selectedChoices)) {
            $this->addFlash('error', 'Veuillez sélectionner au moins un choix.');
            return $this->redirectToRoute('vote', ['id' => $id]);
        }

        // Enregistrer chaque vote dans la base de données
        foreach ($selectedChoices as $choiceId) {
            $choice = $entityManager->getRepository(Choix::class)->find($choiceId);
            if (!$choice) {
                continue;
            }

            $vote = new Vote();
            $vote->setCreatedAt(new \DateTimeImmutable());
            $vote->setAdresseIp($request->getClientIp()); // Récupérer l'adresse IP de l'utilisateur
            $vote->setIdChoix($choice);
            $vote->setIdUser($user); // Associe un utilisateur si nécessaire

            $entityManager->persist($vote);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Votre vote a été enregistré avec succès.');
        return $this->redirectToRoute('home');
    }

    #[\Symfony\Component\Routing\Annotation\Route('/voter/{id}', name: 'vote')]
    public function vote(int $id, SondageRepository $sondageRepository, UtilisateurRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        // Récupérer le sondage par son ID
        $sondage = $sondageRepository->find($id);
        if (!$sondage) {
            throw $this->createNotFoundException('Sondage introuvable.');
        }

        // Rendre la vue avec les détails du sondage et les choix associés
        return $this->render('vote/new.html.twig', [
            'sondage' => $sondage,
            'users' => $users,
            'choices' => $sondage->getChoix(), // Les choix associés au sondage
        ]);
    }


}
