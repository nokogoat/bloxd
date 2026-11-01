<?php

namespace App\DataFixtures;

use App\Entity\Genre;
use App\Entity\Jeu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class JeuFixtures extends Fixture implements DependentFixtureInterface
{
    // titre, studio, genre, année, couleur, description
    private const JEUX = [
        ['Adopt Me!', 'Uplift Games', 'Roleplay', 2017, '#ff7eb6', 'Adopte des animaux, échange-les, décore ta maison. Le jeu qui a fait exploser les échanges d\'oeufs légendaires.'],
        ['Brookhaven RP', 'Wolfpaq', 'Roleplay', 2020, '#4fa3ff', 'Une ville ouverte où tu choisis ta maison, ta voiture et ton rôle. Pas d\'objectif, juste du roleplay avec tes potes.'],
        ['Blox Fruits', 'Gamer Robot Inc.', 'Combat', 2019, '#8e44ad', 'Inspiré de One Piece : mange des fruits du démon, monte de niveau, affronte des boss et explore trois mers.'],
        ['Pet Simulator 99', 'BIG Games', 'Simulateur', 2023, '#f9c74f', 'Casse des coffres, collectionne des milliers de pets et grind à l\'infini. Addictif et un peu absurde.'],
        ['DOORS', 'LSPLASH', 'Horreur', 2022, '#1f2a44', 'Ouvre 100 portes dans un hôtel hanté. Chaque entité a sa règle, et tu l\'apprends en mourant.'],
        ['Murder Mystery 2', 'Nikilis', 'Mini-jeux', 2014, '#c1121f', 'Un meurtrier, un shérif, des innocents. Des rounds courts et des couteaux de collection qui valent une fortune.'],
        ['Tower of Hell', 'YXCeptional Studios', 'Obby', 2018, '#ff6b35', 'Un obby sans checkpoint, généré aléatoirement, avec un chrono. La rage garantie.'],
        ['Arsenal', 'ROLVe Community', 'Combat', 2015, '#2a9d8f', 'FPS façon Gun Game : chaque kill te donne une nouvelle arme. Nerveux et très bien équilibré.'],
        ['Jailbreak', 'Badimo', 'Roleplay', 2017, '#264653', 'Flic ou criminel : évade-toi, braque des banques, fuis en hélico. Un classique qui se met à jour depuis des années.'],
        ['Piggy', 'MiniToon', 'Horreur', 2020, '#e76f51', 'Peppa Pig version cauchemar. Échappe-toi de la maison avant que Piggy te trouve, avec une vraie histoire en chapitres.'],
        ['Bee Swarm Simulator', 'Onett', 'Simulateur', 2018, '#ffd166', 'Élève des abeilles, récolte du pollen, fais du miel. Lent, cosy, et étonnamment profond.'],
        ['Dress to Impress', 'Dress to Impress Group', 'Mini-jeux', 2023, '#ff5fa2', 'Un thème, cinq minutes pour t\'habiller, puis un défilé noté par les autres. Le phénomène de 2024.'],
        ['Grow a Garden', 'Splitting Point Studios', 'Simulateur', 2025, '#43aa8b', 'Plante, attends, récolte, vends. Le jeu continue même quand tu es déconnecté. Record de joueurs simultanés en 2025.'],
        ['Rivals', 'Nosniy Games', 'Combat', 2024, '#06d6a0', 'Un FPS compétitif en 1v1 ou en équipe, gunplay sérieux et classement.'],
        ['99 Nights in the Forest', 'Cuddly Studio', 'Survie', 2025, '#2b6a4a', 'Survis 99 nuits dans une forêt, trouve les enfants disparus, garde le feu allumé. Coop et stressant.'],
        ['Fisch', 'Do Big Studios', 'Simulateur', 2024, '#118ab2', 'Un jeu de pêche étrangement relaxant, avec des poissons mythiques et une météo qui change tout.'],
        ['The Strongest Battlegrounds', 'Yielding Arts', 'Combat', 2022, '#ef233c', 'Combats inspirés de One Punch Man, combos et ultimates. Brutal et sans pitié pour les débutants.'],
        ['Royale High', 'callmehbob', 'Roleplay', 2017, '#cdb4db', 'École de fées et de princesses, tenues à collectionner, mini-jeux de danse. Très communautaire.'],
        ['Natural Disaster Survival', 'Stickmasterluke', 'Survie', 2008, '#f4a261', 'Survis à des tornades, tsunamis et météorites sur une petite île. Un des plus vieux jeux encore joués.'],
        ['Work at a Pizza Place', 'Dued1', 'Tycoon', 2008, '#e63946', 'Cuisine, caisse, livraison : fais tourner une pizzeria avec des inconnus. La nostalgie de Roblox.'],
        ['Theme Park Tycoon 2', 'Den_S', 'Tycoon', 2012, '#9b5de5', 'Construis ton parc d\'attractions avec des montagnes russes très détaillées. Du Planet Coaster gratuit.'],
        ['Dungeon Quest', 'vCaffy', 'Aventure', 2018, '#577590', 'Donjons en coop, loot, niveaux. Un action RPG simple mais efficace.'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::JEUX as $i => [$titre, $studio, $genre, $annee, $couleur, $description]) {
            $jeu = new Jeu();
            $jeu->setTitre($titre)
                ->setStudio($studio)
                ->setGenre($this->getReference('genre-'.$genre, Genre::class))
                ->setAnneeSortie($annee)
                ->setCouleur($couleur)
                ->setDescription($description);
            $manager->persist($jeu);
            $this->addReference('jeu-'.$i, $jeu);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [GenreFixtures::class];
    }
}
