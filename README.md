# Famille

Application web familiale de planning des repas, hébergée sur [famille.laurents.fr](https://famille.laurents.fr).

Chaque famille rejoint ou crée son espace via un code famille lors de l'inscription ; toutes les données (planning, courses, banque de plats, stock, historique du chat IA) sont isolées par famille.

## Fonctionnalités

- **Planning** : grille hebdomadaire des repas (midi/soir, plats + desserts), glisser-déposer ou sélection tactile depuis une banque de plats filtrable, navigation par semaine/mois.
- **Courses** : liste de courses générée automatiquement à partir du planning du mois, ajout/suppression manuels, suivi du stock à cocher.
- **Assistant cuisine (IA)** : chat propulsé par l'API DeepSeek, limité aux sujets culinaires, propose des plats structurés (nom + ingrédients) que l'utilisateur ajoute explicitement à sa banque de plats.
- **Présence** : indication des membres de la famille actuellement en ligne.
- **Authentification par famille** : inscription avec code famille (rejoint une famille existante ou en crée une nouvelle), isolation stricte des données entre familles.
- **PWA** : installable sur mobile, manifest + service worker.

## Stack technique

- [Laravel 13](https://laravel.com) + [Livewire 4](https://livewire.laravel.com) (composants classiques, pas de single-file components)
- [Tailwind CSS v4](https://tailwindcss.com) (configuration CSS-first via `@theme`)
- [Vite](https://vitejs.dev) pour le build des assets front
- MariaDB
- API [DeepSeek](https://api-docs.deepseek.com/) (compatible OpenAI) pour l'assistant cuisine

## Installation locale

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

Renseigner `DEEPSEEK_API_KEY` dans `.env` pour activer l'assistant cuisine (optionnel — l'app fonctionne sans).

## Tests

```bash
php artisan test
```
