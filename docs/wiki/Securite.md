# Sécurité

Cette page documente le modèle de sécurité de l'application et les constats d'une revue de code menée le 2026-07-28. Elle est destinée à être tenue à jour à chaque évolution notable plutôt qu'à rester un instantané figé.

## Modèle général

- **Isolation par `family_id`** : chaque donnée métier (plats, repas planifiés, stock, liste de courses, historique IA) appartient à une famille. Le filtrage se fait à la main dans chaque composant Livewire via `auth()->user()->family_id` — il n'existe pas de policy Laravel ni de global scope Eloquent centralisant la règle (voir [Architecture](Architecture.md)). Chaque nouvelle requête doit donc reproduire ce filtrage explicitement, y compris sur les écritures (`create`, `update`, `updateOrCreate`, `delete`).
- **Authentification** : session Laravel classique, mots de passe hachés (bcrypt, `BCRYPT_ROUNDS=12`), régénération de session après connexion/inscription.
- **Autorisation** : middleware `auth` sur toutes les routes applicatives, `guest` sur connexion/inscription.

## Constats de cette revue

### 1. [Corrigé le 2026-07-28] Contrainte d'unicité de `planned_meals` non alignée avec l'isolation par famille

`database/migrations/2026_07_24_163244_add_course_and_position_to_planned_meals_table.php` définit :

```php
$table->unique(['date', 'meal_slot', 'course', 'position']);
```

Quand `family_id` a été ajouté à la table (`2026_07_24_182247_add_family_id_to_existing_tables.php`), cet index n'a **pas** été refait pour inclure `family_id` — contrairement à `shopping_list_overrides`, dont l'unicité a bien été étendue dans la même migration. Vérifié sur la base de production : l'index `planned_meals_date_meal_slot_course_position_unique` porte toujours uniquement sur `(date, meal_slot, course, position)`.

`PlanificateurRepas::placerPlat()` (`app/Livewire/PlanificateurRepas.php:61`) fait :

```php
RepasPlanifie::updateOrCreate(
    ['family_id' => $this->identifiantFamille(), 'date' => $date, 'meal_slot' => $creneau, 'course' => $service, 'position' => $position],
    ['dish_id' => $plat->id],
);
```

Tant qu'une seule famille existe, aucun symptôme n'est visible. Dès qu'une **deuxième famille** planifie un repas sur la même date + créneau + type de plat + position qu'une autre famille (ce qui est le cas courant : deux familles planifiant chacune leur déjeuner du même jour), `updateOrCreate` ne retrouve pas la ligne existante (elle appartient à une autre famille) et tente une insertion qui viole la contrainte d'unicité globale → erreur SQL (duplicate entry), remontée comme erreur applicative pour l'utilisateur qui essaie de planifier en second.

**Correctif appliqué** : migration `2026_07_28_055840_fix_planned_meals_unique_index_scope_famille`, qui recrée l'unicité en incluant `family_id`, sur le modèle de ce qui existe déjà pour `shopping_list_overrides` :

```php
Schema::table('planned_meals', function (Blueprint $table) {
    $table->dropUnique(['date', 'meal_slot', 'course', 'position']);
    $table->unique(['family_id', 'date', 'meal_slot', 'course', 'position']);
});
```

Exécutée en production le 2026-07-28 (sans risque de conflit : une seule famille existait à ce moment-là, donc aucune ligne ne pouvait violer le nouvel index, plus permissif que l'ancien). Index vérifié en base après migration : `planned_meals_family_id_date_meal_slot_course_position_unique`.

### 2. [Modéré] Code famille : ni longueur minimale, ni validation de complexité, ni approbation

`Inscription::register()` (`app/Livewire/Auth/Inscription.php:41`) valide `family_code` avec `['required', 'string', 'max:255']` seulement — aucune longueur minimale. `Famille::trouverOuCreerParCode()` rejoint silencieusement la famille existante si le code correspond, sans confirmation d'un membre déjà présent.

Le code famille agit donc comme un secret partagé donnant accès complet aux données d'une famille (planning, courses, stock, historique de chat IA) dès l'inscription. Le seul frein est le rate limiting sur l'inscription (10 tentatives/minute par IP, `Inscription.php:26`), insuffisant contre une tentative répartie dans le temps ou sur plusieurs IP si le code choisi est court/prévisible.

Pistes (à arbitrer selon le niveau de risque accepté pour l'usage réel — cercle familial fermé vs ouverture plus large) : longueur minimale sur le code, et/ou évolution vers un système d'invitation (lien ou jeton à usage unique généré par un membre existant) plutôt qu'un code statique et partagé.

### 3. [Modéré] Catalogue d'ingrédients global : édition non restreinte

`ingredients` n'a pas de `family_id` (catalogue partagé, par choix — voir `app/Livewire/GestionCuisine.php:185-189`). La **suppression** est bloquée tant que l'ingrédient est utilisé quelque part, y compris par une autre famille. L'**édition** (`editerIngredient` / `enregistrerIngredient`, `GestionCuisine.php:143-179`) n'a pas ce garde-fou : n'importe quel utilisateur connecté, de n'importe quelle famille, peut renommer ou recatégoriser un ingrédient utilisé par une autre famille.

Impact limité (pas de fuite de données, cohérence du catalogue partagé plutôt qu'un problème de confidentialité) mais à confirmer comme comportement assumé plutôt que comme oubli, vu la règle générale du projet de scoper systématiquement par famille.

### 4. [À arbitrer] Donnée personnelle dans l'historique Git

Une ancienne migration déjà présente sur `origin/main` contient en clair, comme valeur de données (pas comme secret technique), le nom d'une famille réelle utilisé lors de la bascule multi-famille. La retirer proprement demanderait une réécriture d'historique (rebase + force-push), une opération destructive à ne mener qu'à la demande explicite et en connaissance des conséquences (réécrit les hash de commits déjà publiés). Non traité dans le cadre de ce commit ; signalé pour décision.

## Bonnes pratiques déjà en place

- Rate limiting sur connexion (5 tentatives/60s par email+IP), inscription (10/60s par IP) et assistant IA (10 messages/min par famille).
- `APP_DEBUG=false` en production (pas de fuite de stack trace).
- Validation stricte des entrées utilisateur sur tous les formulaires Livewire (`validate()`, `Rule::in`, `Rule::exists`, `Rule::unique`).
- Les valeurs générées par l'IA (type de plat, catégorie d'ingrédient) sont validées contre les listes canoniques avant stockage, jamais insérées telles quelles.
- Mots de passe hachés (bcrypt), jamais loggés ni exposés (`#[Hidden(['password', 'remember_token'])]` sur `User`).
- Aucune sortie Blade non échappée (`{!! !!}`) trouvée dans les vues du projet.
- `.env` correctement ignoré par Git ; seul `.env.example` (sans valeurs réelles) est versionné.
- CSRF géré par défaut (Livewire + `@csrf` sur le formulaire de déconnexion classique).

## Recommandations complémentaires (configuration serveur, hors dépôt)

- `SESSION_SECURE_COOKIE` n'est pas positionné dans `.env` (donc non forcé) alors que l'application est servie en HTTPS (`APP_URL=https://...`) : le mettre à `true` en production pour empêcher l'envoi du cookie de session en clair si jamais une requête HTTP passait au travers.
- Vérifier que l'accès aux logs applicatifs (`Log::error`/`Log::warning`, qui incluent des extraits d'entrées utilisateur) reste restreint à l'équipe technique.
