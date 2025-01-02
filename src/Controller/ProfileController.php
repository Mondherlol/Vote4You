<?php

namespace App\Controller;

use App\Entity\Sondage;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\ChoixRepository;
use App\Repository\CommentaireRepository;
use App\Repository\SondageRepository;
use App\Repository\UserRepository;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

//#[IsGranted("ROLE_USER")]
#[Route('/profile')]
class ProfileController extends AbstractController
{

    #[Route('/{id}/show', name: 'app_profile_show')]
    public function show(int $id, SondageRepository $sondageRepository)
    {
        $user = $this->getUser();
        $roles = $user->getRoles(); // Vérifiez que ceci retourne un tableau


        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_profile_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/sondages', name: 'app_profile_sondages')]
    public function sondages(int $id, SondageRepository $sondageRepository): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que l'ID dans l'URL correspond à l'utilisateur connecté
        if ($user->getId() !== $id) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('home');
        }

        // Récupérer les sondages de l'utilisateur
        $sondages = $sondageRepository->findBy(['owner' => $user]);

        // Rendre la vue pour afficher les sondages
        return $this->render('profile/sondage.html.twig', [
            'user' => $user,
            'sondages' => $sondages,
        ]);
    }

    #[Route('/{id}/sondage/{idS}', name: 'app_profile_sondage_show')]
    public function sondageShow(int $idS,SondageRepository $sondageRepository,CommentaireRepository $commentaireRepository, VoteRepository $voteRepository,ChoixRepository $choixRepository, EntityManagerInterface $entityManager,UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $sondage = $sondageRepository->find($idS);
        // Récupérer les choix du sondage
        $choix = $choixRepository->findBySondageId($idS);
        // Construire un tableau associatif pour chaque choix avec le nombre de votes
        $choixAvecVotes =[];
        if($choix !== null)
        {
            foreach ($choix as $choixItem) {
                // Compter le nombre de votes associés à ce choix
                $listeChoix=$voteRepository->findAll();
                $voteCount=0;
                foreach ($listeChoix as $choixItem2) {
                    if($choixItem2->getId() == $choixItem->getId() ){
                        $voteCount++;
                    }
                }
                // Ajouter le choix et son nombre de votes au tableau
                $choixAvecVotes [] = [
                    'id' => $choixItem->getId(),
                    'titre' => $choixItem->getTitre(),
                    'image' => $choixItem->getImageChoix(),
                    'votes' => (int)$voteCount,
                ];
            }
            // Récupérer les commentaires du sondage

        }


        $commentaires=$commentaireRepository->findBy(['idSondage'=>$sondage]);
        $commentaireAvecUtilisateur =[];
        if($commentaires !== null)
        {
            foreach ($commentaires as $commentaire) {
                $owner=$commentaire->getOwner();
                $commentaireAvecUtilisateur[]=[
                    'id' => $commentaire->getId(),
                    'username' => $owner->getUsername(),
                    'texte' => $commentaire->getTexte(),
                ];


            }
        }



        return $this->render('profile/sondageShow.html.twig', [
            'user' => $user,
            'sondage' => $sondage,
            'choix' => $choixAvecVotes,
            'commentaires' => $commentaireAvecUtilisateur,
        ]);
    }
    #[Route('/{id}', name: 'app_profile_index')]
    public function index(int $id, SondageRepository $sondageRepository): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que l'ID dans l'URL correspond à l'utilisateur connecté
        if ($user->getId() !== $id) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('home');
        }

        // Récupérer les sondages créés par l'utilisateur
        $sondages = $sondageRepository->findBy(['owner' => $user]);

        // Rendre les données du profil et les sondages
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'sondages' => $sondages,
        ]);
    }



}
