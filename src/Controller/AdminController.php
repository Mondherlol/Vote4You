<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Form\AdminType;
use App\Repository\AdminRepository;
use App\Repository\SignalementRepository;
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

#[IsGranted("ROLE_ADMIN")]

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route(name: 'app_admin_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager,AdminRepository $adminRepository,SondageRepository $sondageRepository,VoteRepository $voteRepository,ThemeRepository $themeRepository,UserRepository $userRepository,SignalementRepository $signalementRepository): Response
    {
        $countSondage=$sondageRepository->count();
        $countVote=$voteRepository->count();
        $countTheme=$themeRepository->count();

        $listeClients = $userRepository->findAll();
        $countClient=0;
        foreach ($listeClients as $client) {
            if (in_array('ROLE_USER', $client->getRoles())) {
                $countClient++;
            }
        }

        // Sondages par theme -Chart
        // Récupération du nombre de sondages par thème (relation Many-to-Many)
        $query = $entityManager->createQuery('
        SELECT t.libelle AS themeLibelle, COUNT(s.id) AS sondageCount
        FROM App\Entity\Theme t
        LEFT JOIN t.sondages s
        GROUP BY t.id
    ');

        $themeData = $query->getResult(); // Renvoie un tableau d'associations [themeLibelle => sondageCount]

        // Préparer un tableau associatif pour les résultats
        $sondageParTheme = [];
        foreach ($themeData as $data) {
            $sondageParTheme[$data['themeLibelle']] = $data['sondageCount'];
        }

        //Sondage par mois- Chart

        $qb = $entityManager->createQuery('
        SELECT MONTH(s.createdAt) as mois, COUNT(s.id) as nombre
        FROM App\Entity\Sondage s
        WHERE YEAR(s.createdAt) = YEAR(CURRENT_DATE())
        GROUP BY mois
        ORDER BY mois ASC
        ');
         $result=$qb->getResult();

        // Créez des labels et des valeurs pour chaque mois
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $values = array_fill(0, 12, 0);

        foreach ($result as $item) {
            $mois = (int) $item['mois'];
            $values[$mois - 1] = (int) $item['nombre'];
        }

        // Création du tableau associatif final
        $sondageParMois = [];
        foreach ($labels as $index => $label) {
            $sondageParMois[$label] = $values[$index]; // Associe le label (mois) à la valeur
        }

        //liste signalements
        $listeSignalements=$signalementRepository->findAll();

        //liste themes
        $listeThemes=$themeRepository->findAll();





        return $this->render('admin/index.html.twig', [
            'admins' => $adminRepository->findAll(),
            'countSondage' => $countSondage,
            'countVote' => $countVote,
            'countClient' => $countClient,
            'countTheme' => $countTheme,
            'sondageParTheme' => $sondageParTheme,
            'sondageParMois' => $sondageParMois,
            'listeSignalements' => $listeSignalements,
            'listeThemes' => $listeThemes,


        ]);
    }

    #[Route('/new', name: 'app_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $admin = new Admin();
        $form = $this->createForm(AdminType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($admin);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/new.html.twig', [
            'admin' => $admin,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_show', methods: ['GET'])]
    public function show(Admin $admin): Response
    {
        return $this->render('admin/show.html.twig', [
            'admin' => $admin,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Admin $admin, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/edit.html.twig', [
            'admin' => $admin,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Admin $admin, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$admin->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($admin);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
