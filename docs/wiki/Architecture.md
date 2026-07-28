# Architecture

## Structure du code

```
app/
  Http/Middleware/MettreAJourDerniereActivite.php  # met à jour last_seen_at à chaque requête (présence en ligne)
  Livewire/                                        # un composant par écran
    Accueil.php
    AssistantCuisine.php                           # chat IA
    GestionCuisine.php                              # CRUD plats & ingrédients
    ListeCourses.php
    PlanificateurRepas.php
    Auth/Connexion.php
    Auth/Inscription.php
  Models/                                           # Eloquent
  Services/ClientDeepSeek.php                       # client HTTP vers l'API DeepSeek
resources/views/
  layouts/app.blade.php                             # layout commun + navigation basse
  livewire/                                         # une vue par composant, même arborescence que app/Livewire
routes/web.php                                       # toutes les routes, groupées par middleware guest/auth
database/migrations/
config/emoji.php                                     # listes canoniques (types de plat, catégories d'ingrédient, emojis)
```

Convention Livewire : composants « classiques » (classe PHP + fichier Blade séparé dans `resources/views/livewire/`), pas de single-file components.

## Modèle de données

```
families (id, name, code UNIQUE)
  └─ users (id, name, email UNIQUE, password, family_id → families NULL ON DELETE, last_seen_at)
  └─ dishes            [Plat]              (id, family_id, name, type, low_carb, dessert_suggestion, notes)
  └─ planned_meals     [RepasPlanifie]     (id, family_id, date, meal_slot, course, position, dish_id → dishes NULL ON DELETE)
  └─ shopping_list_overrides [ModificationListeCourses] (id, family_id, month, ingredient_id, included)
  └─ ingredient_stocks [StockIngredient]   (id, family_id, ingredient_id, in_stock)
  └─ ai_messages       [MessageIa]         (id, family_id, role, content, dish JSON, added)

ingredients (id, name UNIQUE, category)   # catalogue global, PAS de family_id
  └─ dish_ingredient (dish_id, ingredient_id)  # pivot plats ↔ ingrédients
```

Points clés :

- **`ingredients` est un catalogue global**, partagé entre toutes les familles (une même « tomate » sert à tout le monde). Toutes les autres tables métier sont scopées par `family_id`.
- `meal_slot` ∈ `{midi, soir}`, `course` ∈ `{plat, dessert}` ; `position` permet plusieurs plats sur un même créneau (ex. plat + accompagnement).
- Les clés étrangères `family_id` sont `nullOnDelete` : la suppression d'une famille ne supprime pas en cascade ses plats/repas/etc., elle les détache (ils devraient être nettoyés séparément si besoin).

## Isolation multi-famille

Chaque composant Livewire qui lit ou écrit des données métier le fait via `auth()->user()->family_id`, systématiquement injecté dans les clauses `where()` (lecture) et dans les attributs `create()`/`updateOrCreate()` (écriture). C'est la seule barrière d'isolation : il n'y a pas de policy Laravel ni de global scope Eloquent centralisant la règle — chaque nouveau composant doit reproduire ce filtrage explicitement (rappelé dans `AGENTS.md`).

Voir [Sécurité](Securite.md) pour les constats de la revue sur ce point, notamment une contrainte d'unicité qui n'est aujourd'hui pas alignée avec ce modèle.

## Assistant IA

`AssistantCuisine` envoie l'historique de conversation (12 derniers messages + prompt système) à `ClientDeepSeek::chat()`, qui appelle l'API DeepSeek (modèle `deepseek-v4-flash`, 800 tokens max, timeout 30s). Le prompt système cadre l'assistant sur des sujets culinaires et lui demande de terminer sa réponse par un bloc ```` ```dish ```` (JSON) quand un plat complet est proposé. Ce bloc est extrait côté serveur (`extractDish`), et le plat n'est créé en base qu'après action explicite de l'utilisateur (`addDish`) — l'IA ne peut jamais écrire en base directement.

Les valeurs `type` et `category` renvoyées par l'IA sont validées contre les listes canoniques de `config/emoji.php` avant d'être stockées ; toute valeur hors liste est neutralisée (`null`) et loggée (`assistant_cuisine.type_hors_liste` / `categorie_hors_liste`) plutôt que d'être insérée telle quelle.
