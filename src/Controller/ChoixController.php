<?php

namespace App\Controller;

use App\Entity\Choix;
use App\Form\ChoixType;
use App\Repository\ChoixRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/choix')]
final class ChoixController extends AbstractController
{
    #[Route(name: 'app_choix_index', methods: ['GET'])]
    public function index(ChoixRepository $choixRepository): Response
    {
        return $this->render('choix/index.html.twig', [
            'choixes' => $choixRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_choix_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $choix = new Choix();
        $form = $this->createForm(ChoixType::class, $choix);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($choix);
            $entityManager->flush();

            return $this->redirectToRoute('app_choix_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('choix/new.html.twig', [
            'choix' => $choix,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_choix_show', methods: ['GET'])]
    public function show(Choix $choix): Response
    {
        return $this->render('choix/show.html.twig', [
            'choix' => $choix,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_choix_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Choix $choix, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChoixType::class, $choix);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_choix_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('choix/edit.html.twig', [
            'choix' => $choix,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_choix_delete', methods: ['POST'])]
    public function delete(Request $request, Choix $choix, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$choix->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($choix);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_choix_index', [], Response::HTTP_SEE_OTHER);
    }
}
