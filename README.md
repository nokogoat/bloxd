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
- Connecté : poster un avis (un seul par jeu), le modifier, le supprimer.
- Admin (`/admin`) : CRUD des jeux et des genres, tableau de bord avec des routes JSON :
  - `GET /admin/api/stats` compteurs et répartition des notes
  - `GET /admin/api/top-jeux` les 10 jeux les mieux notés
  - `GET /admin/api/avis` les 50 derniers avis, `DELETE /admin/api/avis/{id}` pour modérer
  - `GET /admin/api/utilisateurs`, `POST /admin/api/utilisateurs/{id}/admin` pour donner ou retirer le rôle admin

## Organisation

- `src/Entity` : User, Genre, Jeu, Avis
- `src/Controller` : HomeController, JeuController (liste + fiche + formulaire d'avis), AvisController (modifier / supprimer), ProfilController
- `src/Controller/Admin` : AdminController, JeuController et GenreController (CRUD), ApiController (routes JSON)
- `src/Form` : JeuType, GenreType, AvisType, RegistrationFormType
- `src/DataFixtures` : UserFixtures, GenreFixtures, JeuFixtures, AvisFixtures
