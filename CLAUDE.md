# LET'S MANU SERVICES — site web

Site vitrine B2B de LET'S MANU SERVICES (SARL, Abidjan, créée en 2022) : services industriels et construction métallique en Côte d'Ivoire. Le cahier des charges complet est dans `docs/` — c'est la référence pour toute décision de contenu ou de fonctionnalité.

## Ce qu'il faut savoir avant de toucher au site

- **Deux pôles seulement** : services industriels (pôle principal) et construction métallique. BTP et électricité sont des prestations du pôle services industriels, pas des pôles. Ne jamais afficher tout l'objet social ; les autres activités tiennent en une ligne « Autres prestations sur demande » dans À propos.
- **Objectif n° 1 : des demandes de devis** (formulaire, WhatsApp, appel). Tout écran doit laisser ces trois actions à portée de pouce.
- **Mobile d'abord, data chère** : visiteurs sur Android en 3G/4G. Page d'accueil < 1,5 Mo, aucune vidéo en lecture automatique, images WebP en chargement différé, carte Google Maps chargée au clic seulement.
- **Ton** : professionnel, direct, vouvoiement, phrases courtes. Pas de superlatifs creux ; on prouve par le concret.
- **Ne rien inventer** : pas de faux clients, chiffres, certifications ni citations. Les réalisations livrées avec le site sont marquées `demo: true` (« Projet type ») et doivent être remplacées par de vrais chantiers. Le mot de la gérante reste masqué tant qu'il n'est pas saisi dans l'admin.
- **Hors périmètre** : vente en ligne, paiement Mobile Money, comptes clients, assistant IA (phase 2).

## Coordonnées de référence

| | |
|---|---|
| Raison sociale | LET'S MANU SERVICES (SARL) |
| IDU | CI-2022-0035901 B |
| Gérante | MELEDJE ADJIBA MARIE ANGE LAETHITHIA |
| Siège | Abidjan, Koumassi SICOGI, non loin du Groupe scolaire Collectif 1-2-3-4, villa 625 |
| Téléphone / WhatsApp | 07 47 31 41 39 (+225) |
| E-mail | letsmanou@gmail.com (à remplacer par contact@letsmanu.ci / devis@letsmanu.ci) |
| Domaine visé | letsmanu.ci (+ letsmanu.com en redirection), hébergement Hostinger mutualisé |

Ces valeurs par défaut sont dans `app/config.php` et modifiables dans l'admin (Paramètres).

## Technique

PHP 8.1+ sans framework ni base de données (option B du cahier des charges). Les données sont des fichiers JSON dans `data/`. HTML, CSS et JavaScript écrits à la main, aucune dépendance npm ou Composer, aucun build.

```
index.php            contrôleur frontal (toutes les URL passent par lui)
router.php           routeur pour le serveur PHP de développement uniquement
.htaccess            réécriture, HTTPS, cache, blocage de app/ data/ docs/
app/
  bootstrap.php      démarrage, en-têtes de sécurité (CSP avec nonce)
  config.php         réglages par défaut (coordonnées, pôles, secteurs)
  routes.php         table des URL FR/EN + distribution
  lib/               helpers, stockage JSON, images (GD), e-mail, statistiques, icônes
  content/fr.php     tous les textes français   } une seule arborescence de clés,
  content/en.php     tous les textes anglais    } modifiable dans l'admin (Textes)
  views/             gabarit, partiels, pages publiques, pages d'administration (views/admin/)
  seed/              jeu de départ (projets types, FAQ), lu tant que data/ n'a pas son propre fichier
  admin.php          contrôleur de l'espace d'administration
  contact.php        traitement du formulaire de devis
assets/              css (site, admin, plaquette), js (site, admin), police, illustrations SVG, docs/ (plaquettes PDF)
data/                JSON (réglages, textes modifiés, réalisations, FAQ, demandes, statistiques) — jamais servi
uploads/             photos envoyées depuis l'admin (réalisations/, site/)
docs/                cahier des charges (non servi)
letsmanu-logos/      fichiers sources du logo officiel (non servis, à ne pas déployer)
Dockerfile, docker-compose.yml, docker/    environnement local (PHP 8.3 + Apache)
```

`data/` et `uploads/` sont les seules données vivantes : ne jamais les écraser lors d'un déploiement, ne jamais y laisser de données de test.

### Commandes

```bash
docker compose up -d --build          # lancer le site → http://localhost:8080 (Apache + .htaccess réel)
docker compose logs -f                # journaux Apache et erreurs PHP
docker compose down                   # arrêter
php -S localhost:8000 router.php      # variante sans Docker → http://localhost:8000
php -l fichier.php                    # vérifier la syntaxe d'un fichier
```

Le dossier du projet est monté dans le conteneur : pas de reconstruction après une modification de code. En local (`localhost`), les e-mails ne partent pas : ils sont écrits dans `data/mail-log/`.

Espace d'administration : `/admin`. Au premier accès, `/admin/installation` crée le compte (possible une seule fois, tant que `data/admin.json` n'existe pas).

Régénérer les plaquettes PDF après une modification des textes ou de `app/views/pages/plaquette.php` (site lancé en local) :

```powershell
$edge = "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
& $edge --headless=new --disable-gpu --no-pdf-header-footer --print-to-pdf="$PWD\assets\docs\plaquette-lets-manu-services.pdf" http://localhost:8080/plaquette
& $edge --headless=new --disable-gpu --no-pdf-header-footer --print-to-pdf="$PWD\assets\docs\plaquette-lets-manu-services-en.pdf" http://localhost:8080/en/brochure
```

### Tester

Pas de suite de tests automatisés. Après une modification, vérifier dans le navigateur à 360 px et à 1366 px de large, puis le parcours complet : envoi du formulaire de devis avec une photo (demande visible dans `/admin/demandes`, deux messages dans `data/mail-log/`), ajout d'une réalisation avec photo. `docker compose logs` ne doit montrer aucun avertissement PHP.

### Conventions

- **Textes** : jamais en dur dans les vues. Utiliser `t('cle.sous.cle')` (échappé) ou `tx()` (brut) ; ajouter la clé dans `fr.php` **et** `en.php`. Les listes à puces sont des chaînes multilignes (une ligne = un élément) lues avec `lines()`, ce qui les rend modifiables dans l'admin. Les espaces insécables françaises sont posées automatiquement.
- **Grilles CSS** : toute grille à une colonne sur mobile déclare `grid-template-columns: minmax(0, 1fr)`, sinon un contenu large (formulaire, liste) fait déborder la page à l'horizontale.
- **URL** : toujours `url('page')` et `asset('chemin')` — le site doit fonctionner dans un sous-dossier. Slugs FR et EN dans `app/routes.php`.
- **Sécurité** : échapper toute sortie avec `e()` ; tout POST vérifie `csrf_check()` ; pas de script en ligne sans `nonce` (CSP) ; les fichiers envoyés sont toujours réencodés par GD et renommés.
- **Logo** : logo officiel fourni par l'entreprise dans `letsmanu-logos/` (sources PNG, non servies). Les fichiers web sont dans `assets/img/logo/` (WebP rognés, 180/360/720 px, versions `clair` et `sombre`) et `assets/img/favicons/`. Toujours l'afficher avec `logo('clair' | 'sombre', largeur)` ; ne jamais le redessiner, le recolorer ni recomposer son texte. Version `sombre` sur les fonds foncés (pied de page, administration, couverture de la plaquette).
- **Design** : l'identité vient du logo. Jetons CSS dans `:root` de `assets/css/site.css` : orange `#FD6909` réservé aux actions, vert `#0B7C44` en accent (coches, confirmations, WhatsApp), bleu nuit `#0B1725` / acier `#10212F` pour la structure. **Texte foncé sur orange** (le blanc sur orange ne passe pas le contraste AA) ; blanc sur vert autorisé. Polices auto-hébergées : Montserrat (celle du logo) pour les titres via `--police-titres`, Archivo pour le texte via `--police`. Coins à 3 px, bordures plutôt qu'ombres. Les dessins techniques (`assets/img/illustrations/`, fond `#10212F`) tiennent lieu de photos tant que les vraies ne sont pas fournies ; l'admin (Photos du site) permet de les remplacer. Après un changement de couleur ou de logo, régénérer les plaquettes PDF et `assets/img/partage.png`.
- **Accessibilité** : cibles tactiles ≥ 48 px, texte ≥ 16 px, focus visible, `prefers-reduced-motion` respecté, contenu lisible sans JavaScript.
- **Langue** : code et commentaires en français, comme l'interface d'administration.

## Avant la mise en ligne

Liste détaillée dans `README.md` (déploiement Hostinger, création du compte admin, éléments à valider par l'entreprise : horaires, chiffres clés, liste exacte des prestations, coordonnées GPS, vraies photos et réalisations).
