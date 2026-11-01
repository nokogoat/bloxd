# Bloxd

Projet Symfony pour le module R5.06. Un Letterboxd pour les jeux Roblox : on parcourt un catalogue de jeux,
on met une note sur 5 avec un commentaire, et chaque joueur a son journal d'avis.

## Installation

```
composer install
# adapter DATABASE_URL dans .env ou .env.local
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php bin/console app:roblox:sync   # récupère icônes et stats depuis l'API Roblox (facultatif)
symfony server:start
```

## Comptes des fixtures

| email | mot de passe | rôle |
|---|---|---|
| admin@bloxd.fr | admin | ROLE_ADMIN |
| alice@bloxd.fr | alice | joueur |
| bob@bloxd.fr | bob | joueur |
| chloe@bloxd.fr | chloe | joueur |
| dede@bloxd.fr | dede | joueur |
| emma@bloxd.fr | emma | joueur |

Les fixtures créent 9 genres, 22 jeux et une quarantaine d'avis.

## Ce qu'on peut faire

- Sans compte : accueil (mieux notés, derniers avis), liste des jeux avec recherche et filtre par genre, fiche d'un jeu, journal d'un joueur.
- Connecté : poster un avis (un seul par jeu), le modifier, le supprimer. Chercher un jeu sur Roblox et l'ajouter au catalogue en un clic (le genre est déduit de celui de Roblox).
- Admin (`/admin`) : CRUD des jeux et des genres, import d'un jeu depuis son URL Roblox, tableau de bord avec des routes JSON :
  - `POST /admin/api/jeux/sync` et `POST /admin/api/jeux/{id}/sync` pour rafraîchir icônes, joueurs en ligne et visites
  - `GET /admin/api/stats` compteurs et répartition des notes
  - `GET /admin/api/top-jeux` les 10 jeux les mieux notés
  - `GET /admin/api/avis` les 50 derniers avis, `DELETE /admin/api/avis/{id}` pour modérer
  - `GET /admin/api/utilisateurs`, `POST /admin/api/utilisateurs/{id}/admin` pour donner ou retirer le rôle admin

## API Roblox

Roblox expose des API web publiques, sans clé. `src/Service/RobloxApi.php` les appelle avec le HttpClient de Symfony :

- `apis.roblox.com/universes/v1/places/{placeId}/universe` : retrouve l'identifiant « universe » à partir du numéro dans l'URL du jeu
- `games.roblox.com/v1/games?universeIds=...` : nom, créateur, description, date de création, joueurs en ligne, visites
- `thumbnails.roblox.com/v1/games/icons?universeIds=...` : URL de l'icône

Le titre et la description restent modifiables à la main (les titres Roblox sont pleins d'émojis promotionnels, le service les nettoie à l'import). Si l'API ne répond pas, le site fonctionne quand même avec les jaquettes en couleur.

## Organisation

- `src/Entity` : User, Genre, Jeu, Avis
- `src/Controller` : HomeController, JeuController (liste + fiche + formulaire d'avis), AvisController (modifier / supprimer), ProfilController
- `src/Controller/Admin` : AdminController, JeuController (CRUD + import Roblox) et GenreController (CRUD), ApiController (routes JSON)
- `src/Service/RobloxApi.php` : appels à l'API Roblox ; `src/Command/RobloxSyncCommand.php` : `app:roblox:sync`
- `src/Form` : JeuType, GenreType, AvisType, RegistrationFormType
- `src/DataFixtures` : UserFixtures, GenreFixtures, JeuFixtures, AvisFixtures
