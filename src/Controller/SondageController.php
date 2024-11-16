<?php

namespace App\Controller;

use App\Entity\Choix;
use App\Entity\Sondage;
use App\Entity\Theme;
use App\Form\SondageType;
use App\Repository\SondageRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sondage')]
final class SondageController extends AbstractController
{
    #[Route(name: 'app_sondage_index', methods: ['GET'])]
    public function index(SondageRepository $sondageRepository): Response
    {
        return $this->render('sondage/index.html.twig', [
            'sondages' => $sondageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sondage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ThemeRepository $themeRepository): Response
    {
        $sondage = new Sondage();
        $form = $this->createForm(SondageType::class, $sondage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définir la date de création
            $sondage->setCreatedAt(new \DateTimeImmutable());

            // Sauvegarder le sondage dans la base de données pour générer un ID
            $entityManager->persist($sondage);
            $entityManager->flush();

            // Récupère les données des thèmes depuis la requête (assurez-vous que le champ envoie un tableau de données)
            $themesData = $request->request->all('themes'); // Utilise 'all' pour récupérer les données de type tableau

            if (is_array($themesData)) {
                foreach ($themesData as $themeLibelle) {
                    // Vérifiez si le libellé du thème est non vide
                    if (!empty($themeLibelle)) {
                        // Vérifiez si le thème existe déjà dans la base de données
                        $theme = $themeRepository->findOneBy(['libelle' => $themeLibelle]);

                        // Si le thème n'existe pas, créez un nouveau thème
                        if (!$theme) {
                            $theme = new Theme();
                            $theme->setLibelle($themeLibelle);
                            $entityManager->persist($theme); // Persiste le nouveau thème
                        }

                        // Ajoutez le thème au sondage
                        $sondage->addTheme($theme);
                    }
                }
            }


            // Ajouter les choix associés
            $choixData = $request->request->all('choix');
            foreach ($choixData as $choixItem) {
                if (!empty($choixItem['titreChoix'])) { // Vérifie que le titre est rempli
                    $choix = new Choix();
                    $choix->setTitre($choixItem['titreChoix']);
                    $choix->setImageChoix($choixItem['imageChoix'] ?? null); // Facultatif
                    $choix->setSondage($sondage);
                    $sondage->addChoix($choix);
                    $entityManager->persist($choix);
                }
            }
            // Sauvegarder les relations (sondage et ses thèmes) et finaliser les opérations
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
        ]);
    }

    #[Route('/{id}', name: 'app_sondage_show', methods: ['GET'])]
    public function show(Sondage $sondage): Response
    {
        return $this->render('sondage/show.html.twig', [
            'sondage' => $sondage,
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
