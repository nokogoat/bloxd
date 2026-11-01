<?php

namespace App\Controller;

use App\Repository\AvisRepository;
use App\Repository\JeuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(JeuRepository $jeuRepository, AvisRepository $avisRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'mieuxNotes' => $jeuRepository->mieuxNotes(6),
            'derniersAvis' => $avisRepository->derniers(8),
            'nbJeux' => $jeuRepository->count([]),
        ]);
    }
}
