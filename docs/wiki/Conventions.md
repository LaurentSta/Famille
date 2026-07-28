# Conventions et style

Résumé orienté contributeur des règles détaillées dans `AGENTS.md` (à la racine du dépôt) — s'y référer en cas de doute, c'est la source de vérité.

## Langue

- Textes utilisateur, documentation et commentaires : **français**.
- Noms métier (variables, méthodes, classes, composants, vues, fichiers) créés ou modifiés : français, explicites, sans accent.
- Les noms imposés par PHP/Laravel/Livewire/JS, les API externes, les clés de config et les colonnes déjà déployées ne se traduisent pas sans migration + mise à jour de toutes les références.

## Nommage

- `camelCase` pour les variables et méthodes PHP/JS.
- `PascalCase` pour les classes et composants.
- `kebab-case` pour les vues et ressources web.

## Commentaires

- Expliquer l'intention, les règles métier, les effets de bord, les choix non évidents — pas ce que le code fait déjà de façon lisible.
- PHPDoc sur les classes/méthodes publiques quand leur rôle, paramètres ou retour ne sont pas évidents.
- Mettre à jour un commentaire en même temps que le code qu'il décrit.

## Qualité et sécurité

- Méthodes courtes, responsabilité claire par classe/composant.
- Typer les paramètres, propriétés et retours quand possible.
- Valider systématiquement les données entrantes (règles Livewire `validate()`).
- **Vérifier systématiquement l'isolation des données par `family_id`** sur toute nouvelle requête (lecture et écriture) — voir [Sécurité](Securite.md).
- Éviter la duplication ; extraire un service ou une méthode privée si la logique se répète.
- Ne jamais commettre de secret, clé API ou donnée personnelle dans le dépôt.

## Avant de livrer

```bash
php artisan test                # tests PHP/Laravel concernés
npm run build                   # si un fichier JS/CSS/Blade a changé
rg "ancien_nom"                 # vérifier qu'aucune référence à un ancien nom ne subsiste après un renommage
git diff                        # contrôler que seuls les changements demandés sont inclus
```

## Renommages

- Rechercher toutes les références avant de renommer (imports, routes, vues Blade, composants Livewire, tests, configuration, documentation).
- Renommer le fichier en même temps que la classe/le composant (PSR-4, conventions Livewire).
- Petits lots cohérents, tests exécutés après chaque lot.
