# Contribution

## Flux de base

1. Créer une branche depuis `main`.
2. Développer en respectant les [conventions](Conventions.md) du projet.
3. Ajouter/mettre à jour les tests (`tests/Feature`, `tests/Unit`) pour tout changement de comportement.
4. Exécuter `php artisan test` (et `npm run build` si des assets front ont changé).
5. Vérifier le diff (`git diff`) pour ne livrer que le changement prévu.
6. Ouvrir une Pull Request avec un résumé clair du « pourquoi ».

## Tests

- Framework : PHPUnit, via `php artisan test`.
- Les tests Feature utilisent `Livewire::actingAs()` + `RefreshDatabase` et couvrent systématiquement le scoping par famille (ex. `tests/Feature/GestionCuisineTest.php::test_suppression_d_un_plat_scope_a_la_famille`).
- Toute nouvelle requête lisant ou écrivant une donnée métier doit être accompagnée d'un test qui vérifie l'isolation par `family_id` (voir [Sécurité](Securite.md)).

## Commits

- Messages de commit clairs sur l'intention du changement.
- Un lot cohérent par commit ; éviter de mélanger renommage et changement fonctionnel.
- Ne jamais committer `.env`, une clé API, ou une donnée personnelle (voir [Sécurité](Securite.md)).

## Documentation

- Ce wiki (`docs/wiki/`) documente le projet dans la durée : le mettre à jour avec le code (nouvel écran → entrée dans [Fonctionnalités](Fonctionnalites.md), nouvelle table → [Architecture](Architecture.md), etc.).
- `AGENTS.md` (racine du dépôt) reste la référence normative pour les conventions ; ce wiki en est le miroir pédagogique, pas un remplacement.
