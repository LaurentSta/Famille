# Famille — Wiki

Application web familiale de planning des repas : chaque famille dispose d'un espace isolé (planning, liste de courses, banque de plats, stock, historique de l'assistant IA), rejoint via un code famille lors de l'inscription.

## Sommaire

- [Installation](Installation.md) — mettre le projet en route en local
- [Architecture](Architecture.md) — structure du code, modèle de données, isolation multi-famille
- [Fonctionnalités](Fonctionnalites.md) — tour des écrans et des flux principaux
- [Conventions](Conventions.md) — style de code et règles du projet
- [Sécurité](Securite.md) — modèle de sécurité et constats de revue
- [Contribution](Contribution.md) — comment proposer un changement

## Fonctionnalités en un coup d'œil

- **Planning** — grille hebdomadaire des repas (midi/soir, plats + desserts), sélection depuis une banque de plats filtrable, navigation par semaine/mois.
- **Courses** — liste de courses générée automatiquement à partir du planning du mois, ajout/retrait manuels, suivi du stock à cocher, export en fichier texte.
- **Gestion cuisine** — CRUD des plats et des ingrédients du catalogue (création, édition, suppression).
- **Assistant cuisine (IA)** — chat propulsé par l'API DeepSeek, limité aux sujets culinaires, propose des plats structurés que l'utilisateur ajoute explicitement à sa banque de plats.
- **Présence** — indication des membres de la famille actuellement en ligne.
- **Authentification par famille** — inscription avec code famille (rejoint une famille existante ou en crée une nouvelle), isolation stricte des données entre familles.
- **PWA** — installable sur mobile, manifest + service worker.

## Stack technique

- [Laravel 13](https://laravel.com) + [Livewire 4](https://livewire.laravel.com) (composants classiques, pas de single-file components)
- [Tailwind CSS v4](https://tailwindcss.com) (configuration CSS-first via `@theme`)
- [Vite](https://vitejs.dev) pour le build des assets front
- MariaDB en production, SQLite pour les tests
- API [DeepSeek](https://api-docs.deepseek.com/) (compatible OpenAI) pour l'assistant cuisine
