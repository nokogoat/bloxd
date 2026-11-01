<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Jeu;
use App\Form\AvisType;
use App\Repository\GenreRepository;
use App\Repository\JeuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/jeux')]
class JeuController extends AbstractController
{
    #[Route('', name: 'app_jeu_index', methods: ['GET'])]
    public function index(Request $request, JeuRepository $jeuRepository, GenreRepository $genreRepository): Response
    {
        $q = trim((string) $request->query->get('q'));
        $genre = $request->query->getInt('genre') ? $genreRepository->find($request->query->getInt('genre')) : null;

        return $this->render('jeu/index.html.twig', [
            'jeux' => $jeuRepository->rechercher($q, $genre),
            'genres' => $genreRepository->findBy([], ['nom' => 'ASC']),
            'q' => $q,
            'genreActif' => $genre,
        ]);
    }

    #[Route('/{id}', name: 'app_jeu_show', methods: ['GET', 'POST'])]
    public function show(Jeu $jeu, Request $request, EntityManagerInterface $entityManager): Response
    {
        $monAvis = $this->getUser() ? $jeu->getAvisDe($this->getUser()) : null;
        $form = null;

        // formulaire d'avis seulement si connecté et pas encore d'avis sur ce jeu
        if ($this->getUser() && $monAvis === null) {
            $avis = new Avis();
            $avis->setJeu($jeu)->setAuteur($this->getUser());
            $form = $this->createForm(AvisType::class, $avis);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($avis);
                $entityManager->flush();
                $this->addFlash('success', 'Avis publié !');

                return $this->redirectToRoute('app_jeu_show', ['id' => $jeu->getId()]);
            }
        }

        return $this->render('jeu/show.html.twig', [
            'jeu' => $jeu,
            'monAvis' => $monAvis,
            'form' => $form,
        ]);
    }
}
