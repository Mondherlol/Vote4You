<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        // Obtenez les tags depuis la base de données
        $tags = ['Films', 'Jeux-Vidéos', 'Politique', 'Actualité', 'Sport'];

        // Créez un tableau de sondages factices
        $surveys = [
            [
                'title' => 'Quel est votre film préféré de 2024 ?',
                'description' => 'Votez pour le meilleur film de l’année 2024 parmi une sélection des plus gros succès au box-office.',
                'tags' => ['Films'],
                'image_url' => 'https://www.rogerebert.com/wp-content/uploads/2024/07/The-Ten-Best-Films-of-2022.jpg',
                'user'=>[
                    'name'=>'Motaru',
                    'profile_picture_url'=>'https://static.vecteezy.com/system/resources/previews/036/594/092/non_2x/man-empty-avatar-photo-placeholder-for-social-networks-resumes-forums-and-dating-sites-male-and-female-no-photo-images-for-unfilled-user-profile-free-vector.jpg'
                ],
                'time_remaining' => '3 jours'
            ],
            [
                'title' => 'Meilleur jeu de 2024 ?',
                'description' => 'Aidez-nous à déterminer le jeu vidéo préféré de la communauté pour cette année.',
                'tags' => ['Jeux-Vidéos'],
                'image_url' => 'https://info.haas-avocats.com/hubfs/La%20justice%20confirme%20l’interdiction%20de%20revendre%20des%20jeux%20vidéo%20dématérialisés.png',
                'user'=>[
                    'name'=>'Motaru',
                    'profile_picture_url'=>'https://static.vecteezy.com/system/resources/previews/036/594/092/non_2x/man-empty-avatar-photo-placeholder-for-social-networks-resumes-forums-and-dating-sites-male-and-female-no-photo-images-for-unfilled-user-profile-free-vector.jpg'
                ],
                'time_remaining' => '5 jours'
            ],
            [
                'title'=>'Animé préféré du mois ?',
                'description'=>'Votez pour votre animé préféré de ce mois-ci.',
                'tags'=>['Actualité','Animation'],
                'image_url'=>'https://avo-magazine.com/wp-content/uploads/2023/12/avo-anime-recommendations-winter-2024.jpg',
                'user'=>[
                    'name'=>'Motaru',
                    'profile_picture_url'=>'https://static.vecteezy.com/system/resources/previews/036/594/092/non_2x/man-empty-avatar-photo-placeholder-for-social-networks-resumes-forums-and-dating-sites-male-and-female-no-photo-images-for-unfilled-user-profile-free-vector.jpg'
                ],
                'time_remaining'=>'1 jour'
            ],
            [
                'title' => 'Qui préferez vous voir gagner ?',
                'description' => 'Exprimez votre opinion sur l’état actuel de la politique dans votre région.',
                'tags' => ['Politique','Actualité'],
                'image_url'=> 'https://e3.365dm.com/24/08/2048x1152/skynews-2024-us-election-teaser_6671376.png',
                'user'=>[
                    'name'=>'Motaru',
                    'profile_picture_url'=>'https://static.vecteezy.com/system/resources/previews/036/594/092/non_2x/man-empty-avatar-photo-placeholder-for-social-networks-resumes-forums-and-dating-sites-male-and-female-no-photo-images-for-unfilled-user-profile-free-vector.jpg'
                ],
                'time_remaining' => '2 jours'
            ],

            [
                'title' => 'Meme préféré de cette année ?',
                'description' => 'Votez pour votre meme préféré de l’année 2024.',
                'tags' => ['Actualité'],
                'image_url' => 'https://i.kym-cdn.com/photos/images/newsfeed/002/728/031/990.png',
                'user'=>[
                    'name'=>'Motaru',
                    'profile_picture_url'=>'https://static.vecteezy.com/system/resources/previews/036/594/092/non_2x/man-empty-avatar-photo-placeholder-for-social-networks-resumes-forums-and-dating-sites-male-and-female-no-photo-images-for-unfilled-user-profile-free-vector.jpg'
                ],
                'time_remaining' => '6 heures'
            ],
            [
                'title' => 'Espérance VS Club Africain ?',
                'description' => 'Votez pour votre équipe préférée pour le match de ce soir.',
                'tags' => ['Sport','Actualité'],
                'image_url' => 'https://kapitalis.com/tunisie/wp-content/uploads/2023/06/est-vs-ca-derby-live-streaming.jpg',
                'user'=>[
                    'name'=>'Motaru',
                    'profile_picture_url'=>'https://static.vecteezy.com/system/resources/previews/036/594/092/non_2x/man-empty-avatar-photo-placeholder-for-social-networks-resumes-forums-and-dating-sites-male-and-female-no-photo-images-for-unfilled-user-profile-free-vector.jpg'
                ],
                'time_remaining' => '1 jour'
            ],
        ];

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'tags' => $tags,
            'surveys' => $surveys,
        ]);
    }
}
