<?php

namespace App\Controller;

use App\Entity\Choix;
use App\Entity\Sondage;
use App\Entity\Theme;
use App\Form\SondageType;
use App\Repository\ChoixRepository;
use App\Repository\SondageRepository;
use App\Repository\ThemeRepository;
use App\Repository\UserRepository;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

//use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

#[Route('/sondage')]
final class SondageController extends AbstractController
{
    #[Route(name: 'app_sondage_index', methods: ['GET'])]
    public function index(SondageRepository $sondageRepository): Response
    {
        $sondages = $sondageRepository->findSondagesWithVoteCount();

        return $this->render('sondage/index.html.twig', [
            'sondages' => $sondages,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'app_sondage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ThemeRepository $themeRepository, UploaderHelper $uploaderHelper,UserRepository $userRepository): Response
    {

        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
            $this->addFlash('error', 'Vous devez être connecté pour ajouter un sondage.');
            return $this->redirectToRoute('app_login'); // Remplacez 'app_utilisateur_login' par le nom de votre route de connexion
        }

        // Récupérer les tags dynamiquement et formater en tableau associatif
        $themes = array_map(fn($theme) => [
            'id' => $theme->getId(),
            'libelle' => $theme->getLibelle()
        ], $themeRepository->findAll());

        $sondage = new Sondage();
        $form = $this->createForm(SondageType::class, $sondage);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            // Définir la date de création
            $sondage->setOwner($user);
            $sondage->setCreatedAt(new \DateTimeImmutable());

            // Récupérer les IDs des thèmes sélectionnés
            $themeIds = $request->request->all('themes') ?: [];
            if (!is_array($themeIds)) {
                $themeIds = [];
            }
            // Charger les thèmes correspondants
            $themes = $themeRepository->findBy(['id' => $themeIds]);

            if (count($themes) !== count($themeIds)) {
                $this->addFlash('error', 'Un ou plusieurs thèmes sélectionnés sont invalides.');
                return $this->redirectToRoute('app_sondage_new');
            }

            // Associer les thèmes au sondage
            foreach ($themes as $theme) {
                $sondage->addTheme($theme);
            }

            // Ajouter les choix associés
            $choixData = $request->request->all('choix');
            $choixFiles = $request->files->all('choix');  // Récupère les fichiers

          //Pour chaque choix
            foreach ($choixData as $index => $choixItem) {
                if (!empty($choixItem['titreChoix'])) { // Vérifie que le titre est rempli
                    $choix = new Choix();
                    $choix->setTitre($choixItem['titreChoix']);
                    // Vérifie si un fichier a été téléchargé pour ce choix
                    if (!empty($choixFiles[$index]['imageChoixFile'])) {
                        $imageFile = $choixFiles[$index]['imageChoixFile'];

                        if ($imageFile && $imageFile->isValid()) {
                            $newFilename = uniqid().'.'.$imageFile->guessExtension();
                            $imageFile->move($this->getParameter('uploader.choix_image'), $newFilename);
                            $choix->setImageChoix($newFilename);
                        }
                    }
                    //relier le choix avec le sondage
                    $choix->setSondage($sondage);
                    $sondage->addChoix($choix);
                    $entityManager->persist($choix);
                }
            }
            // Sauvegarder le sondage dans la base de données pour générer un ID
            $entityManager->persist($sondage);
            $entityManager->flush();

            // Ajouter un message de confirmation
            $this->addFlash('success', 'Sondage ajouté avec succès, ainsi que ses choix et thèmes associés !');

            // Rediriger vers une autre page, par exemple, la liste des sondages
            return $this->redirectToRoute('app_sondage_index');

        }

        // Afficher le formulaire
        return $this->render('sondage/new.html.twig', [
            'sondage' => $sondage,
            'form' => $form->createView(),
            'themes' => $themes,
        ]);
    }

    #[Route('/{id}', name: 'app_sondage_show', methods: ['GET'])]
    public function show(Sondage $sondage,VoteRepository $voteRepository, EntityManagerInterface $entityManager,UserRepository $userRepository): Response
    {
        // Récupérer les choix du sondage
        $choix = $sondage->getChoix();
        // Construire un tableau associatif pour chaque choix avec le nombre de votes
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
            $choixAvecVotes[] = [
                'id' => $choixItem->getId(),
                'titre' => $choixItem->getTitre(),
                'image' => $choixItem->getImageChoix(),
                'votes' => (int)$voteCount,
            ];
        }
        // Récupérer les commentaires du sondage

        $commentaires=$sondage->getCommentaires();
        foreach ($commentaires as $commentaire) {
            $owner=$commentaire->getOwner();
            $commentaireAvecUtilisateur[]=[
                'id' => $commentaire->getId(),
                'username' => $owner->getUsername(),
                'texte' => $commentaire->getTexte(),
            ];


        }

        return $this->render('sondage/show.html.twig', [
            'sondage' => $sondage,
            'choix' => $choixAvecVotes,
            'commentaires' => $commentaireAvecUtilisateur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sondage_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sondage $sondage, EntityManagerInterface $entityManager, ThemeRepository $themeRepository): Response
    {
        $form = $this->createForm(SondageType::class, $sondage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Gérer les thèmes associés
            $themesData = $request->request->all('themes'); // Récupérer les thèmes depuis la requête

            // Supprimer les thèmes existants pour les remplacer par les nouveaux
            $sondage->getThemes()->clear();

            if (is_array($themesData)) {
                foreach ($themesData as $themeLibelle) {
                    if (!empty($themeLibelle)) {
                        $theme = $themeRepository->findOneBy(['libelle' => $themeLibelle]);

                        if (!$theme) {
                            $theme = new Theme();
                            $theme->setLibelle($themeLibelle);
                            $entityManager->persist($theme);
                        }

                        $sondage->addTheme($theme);
                    }
                }
            }

            // Gérer les choix associés
            $choixData = $request->request->all('choix');
            // Supprimer les choix existants pour les remplacer par les nouveaux
            foreach ($sondage->getChoix() as $choix) {
                $entityManager->remove($choix);
            }

            foreach ($choixData as $choixItem) {
                if (!empty($choixItem['titreChoix'])) {
                    $choix = new Choix();
                    $choix->setTitre($choixItem['titreChoix']);
                    $choix->setImageChoix($choixItem['imageChoix'] ?? null);
                    $choix->setSondage($sondage);
                    $sondage->addChoix($choix);
                    $entityManager->persist($choix);
                }
            }

            // Sauvegarder toutes les modifications
            $entityManager->flush();

            // Ajouter un message de confirmation
            $this->addFlash('success', 'Sondage mis à jour avec succès, ainsi que ses choix et thèmes associés !');

            return $this->redirectToRoute('app_sondage_index', [], Response::HTTP_SEE_OTHER);
        }
        // Récupérer les thèmes associés pour les passer au template
        $themes = $sondage->getThemes()->map(function ($theme) {
            return $theme->getLibelle();
        })->toArray();

        // Récupérer les choix associés pour les passer au template
        $choix = $sondage->getChoix()->map(function ($choix) {
            return [
                'titreChoix' => $choix->getTitre(),
                'imageChoix' => $choix->getImageChoix(),
            ];
        })->toArray();

        // Afficher le formulaire
        return $this->render('sondage/edit.html.twig', [
            'sondage' => $sondage,
            'form' => $form->createView(),
            'themes' => $themes, // Passer la variable themes au template
            'choix' => $choix, // Passer la variable choix au template

        ]);
    }
    #[Route('/{id}', name: 'app_sondage_delete', methods: ['POST'])]
    public function delete(Request $request, Sondage $sondage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sondage->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sondage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sondage_index', [], Response::HTTP_SEE_OTHER);
    }
}
