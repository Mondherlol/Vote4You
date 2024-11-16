<?php

namespace App\Controller;

use App\Repository\SondageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(SondageRepository $sondageRepository): Response
    {
        // Obtenez les tags depuis la base de données
        // Vous pouvez créer une méthode dans le repository ou utiliser une requête personnalisée
        $tags = ['Films', 'Jeux-Vidéos', 'Politique', 'Actualité', 'Sport'];

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

    private function calculateTimeRemaining($createdAt)
    {
        // Logique pour calculer le temps restant
        $now = new \DateTime();
        $interval = $now->diff($createdAt);
        return $interval->format('%d jours %h heures %i minutes'); // Exemple de format
    }
}
