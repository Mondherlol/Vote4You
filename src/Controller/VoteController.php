<?php

namespace App\Controller;

use App\Entity\Choix;
use App\Entity\User;
use App\Entity\Vote;
use App\Form\VoteType;
use App\Repository\SondageRepository;
use App\Repository\UserRepository;
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
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        VoteRepository $voteRepository
    ): Response {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        if ($user) {
            $this->addFlash('error', 'Vous devez être connecté pour voter.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si l'utilisateur a déjà voté
        $existingVotes = $voteRepository->findBy(['user' => $user]);
        if (!empty($existingVotes)) {
            $this->addFlash('warning', 'Vous avez déjà voté. Souhaitez-vous modifier vos votes ?');

            return $this->render('vote/confirm_revote.html.twig', [
                'existingVotes' => $existingVotes,
            ]);
        }

        // Si l'utilisateur n'a pas encore voté, lui permettre de voter
        $vote = new Vote();
        $form = $this->createForm(VoteType::class, $vote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vote->setCreatedAt(new \DateTimeImmutable());
            $vote->setUser($user); // Associer le vote à l'utilisateur
            $entityManager->persist($vote);
            $entityManager->flush();

            $this->addFlash('success', 'Votre vote a été enregistré avec succès.');
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
    public function submitVote(
        int $id,
        Request $request,
        SondageRepository $sondageRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer le sondage
        $sondage = $sondageRepository->find($id);
        if (!$sondage) {
            throw $this->createNotFoundException('Sondage introuvable.');
        }

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voter.');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les choix sélectionnés
        $selectedChoices = json_decode($request->request->get('choices'), true);
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
            $vote->setAdresseIp($request->getClientIp());
            $vote->setIdChoix($choice);
            $vote->setUser($user); // Associer l'utilisateur connecté

            $entityManager->persist($vote);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Votre vote a été enregistré avec succès.');
        return $this->redirectToRoute('app_sondage_results', ['id' => $id]);
    }


    #[Route('/voter/{id}', name: 'vote')]
    public function vote(
        int $id,
        SondageRepository $sondageRepository,
        VoteRepository $voteRepository,
        UserRepository $userRepository
    ): Response {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voter.');
        }


        // Récupérer le sondage par son ID
        $sondage = $sondageRepository->find($id);
        if (!$sondage) {
            throw $this->createNotFoundException('Sondage introuvable.');
        }

        // Récuperer le créateur du sondage
        $owner = $userRepository->find($sondage->getOwner());

        // Vérifier si l'utilisateur a déjà voté
        $voted = $sondageRepository->hasUserVoted($sondage->getId(), $user->getId());

        if ($voted) {
            // Rediriger vers la page de confirmation si des votes existent
            return $this->render('vote/confirm_revote.html.twig', [
                'sondage' => $sondage,
                'existingVotes' => $voteRepository->findVotesByUserInSondage($user->getId(), $sondage->getId()),
            ]);
        }

        // Si aucun vote existant, afficher la page de vote
        return $this->render('vote/new.html.twig', [
            'sondage' => $sondage,
            'choices' => $sondage->getChoix(),
            'owner' => $owner,
            'comments' => $sondage->getCommentaires(),

        ]);
    }

    #[Route('/reset/{id}', name: 'app_vote_reset', methods: ['POST'])]
    public function resetVotes(
        int $id,
        EntityManagerInterface $entityManager,
        SondageRepository $sondageRepository,
        VoteRepository $voteRepository): Response {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour effectuer cette action.');
            return $this->redirectToRoute('app_login');
        }

        // Récuperer sondage
        $sondage = $sondageRepository->find($id);
        if (!$sondage) {
            throw $this->createNotFoundException('Sondage introuvable.');
        }

        // Vérifier si l'utilisateur a déjà voté
        $voted = $sondageRepository->hasUserVoted($sondage->getId(), $user->getId());

        if(!$voted) {
            $this->addFlash('error', 'Vous n\'avez pas encore voté pour ce sondage.');
            return $this->redirectToRoute('vote', ['id' => $id]);
        }

        // Supprimer les votes de l'utilisateur pour ce sondage
        $votes = $voteRepository->findVotesByUserInSondage($user->getId(), $sondage->getId());
        foreach ($votes as $vote) {
            $entityManager->remove($vote);
        }
        $entityManager->flush();

        $this->addFlash('success', 'Vos anciens votes ont été supprimés. Vous pouvez maintenant revoter.');
        return $this->redirectToRoute('vote',
            ['id' => $sondage->getId()]
        );
    }


}
