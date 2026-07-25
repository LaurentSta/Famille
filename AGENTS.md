# Instructions du projet Famille

## Langue du code

- Écrire les textes destinés aux utilisateurs, la documentation et les commentaires en français.
- Utiliser des noms français, explicites et sans accent pour les éléments métier créés ou modifiés : variables, méthodes, propriétés, classes, composants, vues et fichiers.
- Employer le `camelCase` pour les variables et méthodes PHP/JavaScript, le `PascalCase` pour les classes et composants, et le `kebab-case` pour les vues et ressources web.
- Préserver les noms imposés par PHP, Laravel, Livewire, JavaScript, les API externes, les clés de configuration et les colonnes de base de données déjà déployées. Ne pas les traduire sans migration, mise à jour de toutes les références et vérification de compatibilité.
- Ne pas renommer les dépendances, commandes, conventions de framework ni les fichiers de configuration standard (`composer.json`, `package.json`, `vite.config.js`, etc.).

## Renommages

- Avant un renommage, rechercher toutes les références : imports, routes, vues Blade, composants Livewire, tests, configuration et documentation.
- Renommer un fichier en même temps que la classe ou le composant qu'il contient, conformément à l'autoloading PSR-4 et aux conventions Livewire.
- Effectuer les renommages en petits lots cohérents et exécuter les tests après chaque lot.
- Éviter les traductions littérales ambiguës ; privilégier le vocabulaire métier déjà employé dans l'application.

## Commentaires et documentation

- Expliquer l'intention, les règles métier, les effets de bord et les choix non évidents.
- Ne pas commenter chaque ligne : un commentaire qui répète le code le rend plus difficile à maintenir.
- Ajouter un PHPDoc aux classes et méthodes publiques lorsque leur rôle, leurs paramètres ou leur retour ne sont pas évidents.
- Mettre à jour les commentaires lors de toute modification du code qu'ils décrivent.

## Qualité et sécurité

- Conserver des méthodes courtes et une responsabilité claire par classe ou composant.
- Déclarer les types de paramètres, propriétés et retours lorsque possible.
- Valider les données entrantes et vérifier systématiquement l'isolation des données par `family_id`.
- Éviter la duplication ; extraire un service ou une méthode privée lorsque la logique est réutilisée.
- Ne jamais placer de secret, clé API ou donnée personnelle dans le dépôt.

## Vérification avant livraison

- Exécuter `php artisan test` pour les changements PHP/Laravel concernés.
- Exécuter le build front-end approprié lorsqu'un fichier JavaScript, CSS ou Blade est modifié.
- Vérifier qu'aucune référence à un ancien nom ne subsiste avec `rg`.
- Contrôler le diff afin de s'assurer que seuls les changements demandés sont inclus.
