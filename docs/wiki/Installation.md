# Installation locale

## Prérequis

- PHP (compatible Laravel 13) + Composer
- Node.js + npm
- Une base de données (SQLite suffit en local ; MariaDB en production)

## Mise en route

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

## Variables d'environnement notables

| Variable | Rôle | Obligatoire |
|---|---|---|
| `APP_KEY` | Clé de chiffrement de l'application | Oui — générée par `key:generate` |
| `DB_*` | Connexion base de données | Oui |
| `DEEPSEEK_API_KEY` | Active l'assistant cuisine (IA) | Non — l'app fonctionne sans, l'écran IA affiche juste un message « non configuré » |
| `APP_DEBUG` | Affiche les erreurs détaillées | À laisser à `false` en production |

Aucune variable d'environnement, clé ou secret ne doit être committé : `.env` est ignoré par Git (voir `.gitignore`), seul `.env.example` (sans valeurs réelles) est versionné.

## Développement front

```bash
npm run dev    # Vite en mode watch
npm run build  # build de production des assets
```

## Tests

```bash
php artisan test
```

Les tests utilisent `RefreshDatabase` et tournent sur la configuration de `phpunit.xml` (SQLite en mémoire), indépendamment de la base de développement.
