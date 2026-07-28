# Fonctionnalités

## Planning (`/planning` — `PlanificateurRepas`)

Grille hebdomadaire (7 jours × midi/soir × plat/dessert). Chaque case se remplit en choisissant un plat dans la banque de plats de la famille (filtrable par nom et par type plat/dessert). Navigation par semaine et par mois, avec un indicateur du nombre de créneaux déjà remplis par semaine/mois.

Placer un plat sur une case lève automatiquement une éventuelle exclusion manuelle antérieure de ses ingrédients dans la liste de courses du mois (voir ci-dessous), pour que le retrait d'une recette d'un mois ne « masque » pas silencieusement un ingrédient redevenu nécessaire.

## Courses (`/courses` — `ListeCourses`)

Liste de courses dérivée automatiquement des plats planifiés sur le mois sélectionné (agrégation des ingrédients de tous les plats du planning), groupée par catégorie (`config('emoji.ingredient_category_default')`).

- **Coche « en stock »** : marque un ingrédient comme déjà présent à la maison (`ingredient_stocks`), sans le retirer de la liste.
- **Retrait manuel (croix)** : exclut un ingrédient de la liste du mois même s'il est requis par un plat planifié ; il reste affiché en grisé (pas de disparition silencieuse) et peut être remis en un clic.
- **Ajout manuel** : ajoute un ingrédient du catalogue (ou un nouvel ingrédient) à la liste du mois indépendamment du planning.
- **Export** : téléchargement d'un fichier texte de la liste (ingrédients non retirés, triés alphabétiquement, sans les indicateurs d'écran).

Ces ajouts/retraits manuels sont stockés par mois (`shopping_list_overrides`, clé `family_id + month + ingredient_id`) et se recalculent proprement d'un mois sur l'autre.

## Gestion cuisine (`/gestion` — `GestionCuisine`)

Deux onglets :

- **Plats** — création/édition/suppression des plats de la famille (nom, type, low-carb, suggestion de dessert associé, notes libres, ingrédients liés). Recherche par nom.
- **Ingrédients** — création/édition/suppression des ingrédients du **catalogue global** (nom unique, catégorie). La suppression est bloquée tant que l'ingrédient est référencé par un plat, un stock ou une liste de courses — y compris ceux d'une autre famille, puisque le catalogue est partagé (voir [Sécurité](Securite.md)).

## Assistant cuisine — IA (`/ia` — `AssistantCuisine`)

Chat avec un assistant culinaire (API DeepSeek), cadré pour ne répondre que sur des sujets cuisine. Deux points d'entrée :

- Message libre.
- « Je ne sais pas quoi manger » à partir du stock actuel (aucun appel IA si le stock est vide — réponse directe pour éviter un appel inutile).

Quand l'assistant propose un plat complet (nom + ingrédients), un bouton « Ajouter à mes plats » permet de le créer explicitement dans la banque de plats de la famille ; l'IA ne modifie jamais la base directement. Un anti-doublon rapproche les ingrédients proposés des ingrédients déjà existants (accents, casse, pluriel simple) avant d'en créer de nouveaux.

Limité à 10 messages par minute par famille (`RateLimiter`, clé `assistant-cuisine|{family_id}`).

## Accueil (`/` — `Accueil`)

Salutation (Bonjour/Bonsoir selon l'heure) et liste des membres de la famille avec indicateur de présence (`isOnline()` : `last_seen_at` de moins de 5 minutes, mis à jour à chaque requête par le middleware `MettreAJourDerniereActivite`).

## Authentification par famille (`Connexion` / `Inscription`)

L'inscription demande un **code famille** : `Famille::trouverOuCreerParCode()` rejoint la famille existante si le code correspond (normalisé en majuscules, espaces retirés), ou en crée une nouvelle sinon. Il n'y a pas d'étape de confirmation par un membre existant — voir [Sécurité](Securite.md) pour l'analyse de ce choix.
