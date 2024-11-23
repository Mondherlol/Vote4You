<?php

namespace App\Controller;

use App\Entity\Sondage;
use App\Repository\SondageRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(SondageRepository $sondageRepository, ThemeRepository $themeRepository): Response
    {

        // Récupérer les tags dynamiquement et formater en tableau associatif
        $tags = array_map(fn($theme) => [
            'id' => $theme->getId(),
            'libelle' => $theme->getLibelle()
        ], $themeRepository->findAll());
        // Récupérer tous les sondages de la base de données
        $surveys = $sondageRepository->findAll();

        // Transformez les sondages en un tableau de données approprié pour le template
        $formattedSurveys = [];
        foreach ($surveys as $survey) {
            $formattedSurveys[] = [
                'title' => $survey->getTitre(),
                'description' => $survey->getDescription(),
                'tags' => $survey->getThemes()->map(fn($tag) => $tag->getLibelle())->toArray(),
                'image_url' => $survey->getImage(),
                'user' => [
                    'name' => $survey->getIdOwner()->getPseudo(),
                    'profile_picture_url' => $survey->getIdOwner()->getProfilePic(),
                ],
                'time_remaining' => $this->calculateTimeRemaining($survey->getCreatedAt()), // Méthode pour calculer le temps restant
            ];
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'tags' => $tags,
            'surveys' => $formattedSurveys,
        ]);
    }

    #[Route('/search', name: 'search_survey', methods: ['GET', 'POST'])]
    public function searchSurvey(Request $request, SondageRepository $sondageRepository, ThemeRepository $themeRepository): Response
    {
        // Récupérer les tags dynamiquement et formater en tableau associatif
        $tags = array_map(fn($theme) => [
            'id' => $theme->getId(),
            'libelle' => $theme->getLibelle()
        ], $themeRepository->findAll());
        $query = $request->query->get('query', '');
        $surveys = !empty($query) ? $sondageRepository->findByTitleLike($query) : [];
        $formattedSurveys = [];
        foreach ($surveys as $survey) {
            $formattedSurveys[] = [
                'title' => $survey->getTitre(),
                'description' => $survey->getDescription(),
                'tags' => $survey->getThemes()->map(fn($tag) => $tag->getLibelle())->toArray(),
                'image_url' => $survey->getImage(),
                'user' => [
                    'name' => $survey->getIdOwner()->getPseudo(),
                    'profile_picture_url' => $survey->getIdOwner()->getProfilePic(),
                ],
                'time_remaining' => $this->calculateTimeRemaining($survey->getCreatedAt()), // Méthode pour calculer le temps restant
            ];
        }
        return $this->render('home/index.html.twig', [
            'surveys' => $formattedSurveys,
            'tags' => $tags,
            'query' => $query,
        ]);
    }
    #[Route('/filter/{id}', name: 'filter_by_theme')]
    public function filterByTheme(int $id, SondageRepository $sondageRepository, ThemeRepository $themeRepository): Response
    {
        // Récupérer le thème sélectionné
        $theme = $themeRepository->find($id);
        if (!$theme) {
            throw $this->createNotFoundException('Thème non trouvé.');
        }
        // Récupérer les tags dynamiquement et formater en tableau associatif
        $tags = array_map(fn($theme) => [
            'id' => $theme->getId(),
            'libelle' => $theme->getLibelle()
        ], $themeRepository->findAll());
        $surveys = $sondageRepository->createQueryBuilder('s')
            ->join('s.themes', 't')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

        // Transformez les sondages en un tableau de données approprié pour le template
        $formattedSurveys = [];
        foreach ($surveys as $survey) {
            $formattedSurveys[] = [
                'title' => $survey->getTitre(),
                'description' => $survey->getDescription(),
                'tags' => $survey->getThemes()->map(fn($tag) => $tag->getLibelle())->toArray(),
                'image_url' => $survey->getImage(),
                'user' => [
                    'name' => $survey->getIdOwner()->getPseudo(),
                    'profile_picture_url' => $survey->getIdOwner()->getProfilePic(),
                ],
                'time_remaining' => $this->calculateTimeRemaining($survey->getCreatedAt()), // Méthode pour calculer le temps restant
            ];
        }

        return $this->render('home/index.html.twig', [
            'surveys' => $formattedSurveys,
            'tags' => $tags,
            'query' => $theme->getLibelle(), // Envoyer le libellé du thème comme "query"



        ]);
    }



    private function calculateTimeRemaining($createdAt)
    {
        // Logique pour calculer le temps restant
        $now = new \DateTime();
        $interval = $now->diff($createdAt);
        return $interval->format('%d jours %h heures %i minutes'); // Exemple de format
    }
}
