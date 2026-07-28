# Documentation

Le wiki du projet vit dans [`docs/wiki/`](wiki/Home.md) :

- [Accueil](wiki/Home.md)
- [Installation](wiki/Installation.md)
- [Architecture](wiki/Architecture.md)
- [Fonctionnalités](wiki/Fonctionnalites.md)
- [Conventions](wiki/Conventions.md)
- [Sécurité](wiki/Securite.md)
- [Contribution](wiki/Contribution.md)

## Pourquoi des fichiers Markdown dans `docs/` plutôt que le wiki GitHub natif

Le wiki GitHub (`github.com/<repo>/wiki`) vit dans un dépôt Git séparé (`<repo>.wiki.git`), qui n'existe que si au moins une page y a déjà été créée depuis l'interface web. Garder le contenu ici permet de le versionner avec le code (revue, historique, cohérence garantie avec l'état réel du projet) et de le publier ensuite si besoin :

```bash
git clone git@github.com:LaurentSta/Famille.wiki.git
cp docs/wiki/*.md Famille.wiki/
cd Famille.wiki && git add -A && git commit -m "Import du wiki depuis docs/wiki" && git push
```

À refaire à chaque mise à jour notable de `docs/wiki/`, ou à automatiser via une action CI si le wiki GitHub natif devient la référence.
