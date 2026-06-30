# CI/CD Setup

Ce projet utilise maintenant une pipeline GitHub Actions avec deux jobs :

1. `ci`
- installe les dépendances PHP et Node.js
- crée deux bases MySQL de test : `app_test` et `cms_test`
- lance `php artisan migrate --force`
- exécute `php artisan test`
- build les assets Vite

2. `deploy`
- se lance après `ci` uniquement sur `push` vers `main`
- build les assets front
- synchronise le projet vers le VPS par `rsync`
- exécute `scripts/deploy-production.sh` à distance

## Fichiers ajoutés
- `.github/workflows/ci-cd.yml`
- `scripts/deploy-production.sh`

## Secrets GitHub à configurer
Dans le repository GitHub, ajoute ces secrets :

- `VPS_HOST`
- `VPS_PORT`
- `VPS_USER`
- `VPS_APP_PATH`
- `VPS_SSH_PRIVATE_KEY`

## Valeurs typiques
Exemple :

- `VPS_HOST` = `123.123.123.123`
- `VPS_PORT` = `22`
- `VPS_USER` = `ubuntu`
- `VPS_APP_PATH` = `/var/www/admin.goexploriabusiness.com`
- `VPS_SSH_PRIVATE_KEY` = contenu complet de `~/.ssh/id_ed25519`

## Prérequis sur le VPS
Le serveur doit avoir :

- PHP 8.4+
- Composer
- MySQL/MariaDB
- accès en écriture sur `storage` et `bootstrap/cache`

Le déploiement ne dépend pas de `git pull` sur le VPS, donc il n'y a plus besoin d'auth GitHub sur le serveur pour publier.

## Déclenchement
- `pull_request` vers `main` : CI seulement
- `push` sur `main` : CI + déploiement production
- `workflow_dispatch` : lancement manuel