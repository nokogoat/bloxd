<?php

namespace App\Service;

use App\Entity\Jeu;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Appels aux API web publiques de Roblox (pas de clé nécessaire).
 *
 * - apis.roblox.com/universes/v1/places/{placeId}/universe : placeId -> universeId
 * - games.roblox.com/v1/games?universeIds=...              : infos (nom, créateur, joueurs, visites...)
 * - thumbnails.roblox.com/v1/games/icons?universeIds=...    : icône du jeu
 */
class RobloxApi
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    /**
     * Accepte une URL roblox.com/games/123456/Nom ou directement un nombre.
     */
    public function extrairePlaceId(string $saisie): ?int
    {
        $saisie = trim($saisie);
        if (preg_match('/games\/(\d+)/', $saisie, $m)) {
            return (int) $m[1];
        }
        if (ctype_digit($saisie)) {
            return (int) $saisie;
        }

        return null;
    }

    public function universeId(int $placeId): ?int
    {
        try {
            $data = $this->client->request('GET', "https://apis.roblox.com/universes/v1/places/{$placeId}/universe")->toArray();
        } catch (ExceptionInterface) {
            return null;
        }

        return $data['universeId'] ?? null;
    }

    /**
     * @param int[] $universeIds
     * @return array<int, array<string, mixed>> universeId => données brutes de l'API
     */
    public function infos(array $universeIds): array
    {
        if ($universeIds === []) {
            return [];
        }
        try {
            $data = $this->client->request('GET', 'https://games.roblox.com/v1/games', [
                'query' => ['universeIds' => implode(',', $universeIds)],
            ])->toArray();
        } catch (ExceptionInterface) {
            return [];
        }

        $result = [];
        foreach ($data['data'] ?? [] as $jeu) {
            $result[(int) $jeu['id']] = $jeu;
        }

        return $result;
    }

    /**
     * @param int[] $universeIds
     * @return array<int, string> universeId => URL de l'icône
     */
    public function icones(array $universeIds): array
    {
        if ($universeIds === []) {
            return [];
        }
        try {
            $data = $this->client->request('GET', 'https://thumbnails.roblox.com/v1/games/icons', [
                'query' => [
                    'universeIds' => implode(',', $universeIds),
                    'size' => '512x512',
                    'format' => 'Png',
                    'returnPolicy' => 'PlaceHolder',
                ],
            ])->toArray();
        } catch (ExceptionInterface) {
            return [];
        }

        $result = [];
        foreach ($data['data'] ?? [] as $icone) {
            if (($icone['state'] ?? '') === 'Completed' && !empty($icone['imageUrl'])) {
                $result[(int) $icone['targetId']] = $icone['imageUrl'];
            }
        }

        return $result;
    }

    /**
     * Remplit un nouveau Jeu à partir d'un placeId (titre, studio, description, année).
     * Retourne false si Roblox ne connaît pas ce jeu.
     */
    public function importer(Jeu $jeu, int $placeId): bool
    {
        $universeId = $this->universeId($placeId);
        if ($universeId === null) {
            return false;
        }
        $infos = $this->infos([$universeId])[$universeId] ?? null;
        if ($infos === null) {
            return false;
        }

        $jeu->setPlaceId($placeId)
            ->setUniverseId($universeId)
            ->setTitre(mb_substr($this->nettoyerTitre($infos['name']), 0, 100))
            ->setStudio(mb_substr($infos['creator']['name'] ?? 'Inconnu', 0, 100))
            ->setDescription($infos['description'] ?: 'Pas de description.')
            ->setAnneeSortie((int) (new \DateTimeImmutable($infos['created']))->format('Y'));

        $this->appliquerStats($jeu, $infos, $this->icones([$universeId])[$universeId] ?? null);

        return true;
    }

    /**
     * Met à jour icône, joueurs en ligne et visites de plusieurs jeux en 2 appels (par lots de 50).
     * Ne touche pas au titre ni à la description, qui sont gérés à la main.
     *
     * @param Jeu[] $jeux
     * @return int nombre de jeux mis à jour
     */
    public function synchroniser(array $jeux): int
    {
        $parUniverse = [];
        foreach ($jeux as $jeu) {
            if ($jeu->getPlaceId() === null) {
                continue;
            }
            if ($jeu->getUniverseId() === null) {
                $universeId = $this->universeId($jeu->getPlaceId());
                if ($universeId === null) {
                    continue;
                }
                $jeu->setUniverseId($universeId);
            }
            $parUniverse[$jeu->getUniverseId()] = $jeu;
        }

        $nb = 0;
        foreach (array_chunk(array_keys($parUniverse), 50) as $lot) {
            $infos = $this->infos($lot);
            $icones = $this->icones($lot);
            foreach ($lot as $universeId) {
                if (!isset($infos[$universeId])) {
                    continue;
                }
                $this->appliquerStats($parUniverse[$universeId], $infos[$universeId], $icones[$universeId] ?? null);
                $nb++;
            }
        }

        return $nb;
    }

    private function appliquerStats(Jeu $jeu, array $infos, ?string $icone): void
    {
        $jeu->setJoueursEnLigne($infos['playing'] ?? null)
            ->setVisites($infos['visits'] ?? null)
            ->setMajRoblox(new \DateTimeImmutable());
        if ($icone !== null) {
            $jeu->setImageUrl($icone);
        }
    }

    /**
     * Les titres Roblox sont pleins de "[🎃 UPDATE]" et d'émojis promotionnels, on les enlève.
     */
    private function nettoyerTitre(string $titre): string
    {
        $titre = preg_replace('/\[[^\]]*\]/u', '', $titre);
        $titre = preg_replace('/[^\p{L}\p{N}\s\'!?:.\-&]/u', '', $titre);

        return trim(preg_replace('/\s+/', ' ', $titre)) ?: 'Sans titre';
    }
}
