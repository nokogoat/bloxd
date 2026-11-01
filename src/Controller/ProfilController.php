<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil/{pseudo}', name: 'app_profil', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['pseudo' => 'pseudo'])] User $user): Response
    {
        $avis = $user->getAvis();
        $moyenne = null;
        if (!$avis->isEmpty()) {
            $total = 0;
            foreach ($avis as $a) {
                $total += $a->getNote();
            }
            $moyenne = round($total / $avis->count(), 1);
        }

        return $this->render('profil/show.html.twig', [
            'profil' => $user,
            'moyenne' => $moyenne,
        ]);
    }
}
