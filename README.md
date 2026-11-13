# Site web LET'S MANU SERVICES

Site vitrine en français et en anglais : services industriels et construction métallique, demande de devis, galerie de réalisations, espace d'administration. PHP 8.1+ sans base de données, prévu pour un hébergement mutualisé Hostinger.

## Voir le site sur votre poste

### Avec Docker (recommandé)

```bash
docker compose up -d --build     # première fois, ou après une modification du Dockerfile
```

Ouvrez ensuite **http://localhost:8080**. L'administration est à l'adresse **http://localhost:8080/admin**.

| Besoin | Commande |
|---|---|
| Démarrer | `docker compose up -d` |
| Arrêter | `docker compose down` |
| Voir les journaux | `docker compose logs -f` |

Le dossier du projet est monté dans le conteneur : une modification de fichier est visible dès le rechargement de la page. En local, aucun e-mail ne part réellement : chaque message est écrit dans `data/mail-log/` pour vérification.

### Sans Docker

```bash
php -S localhost:8000 router.php
```

## Première connexion à l'administration

1. Ouvrez `/admin` : le site propose de créer le compte (identifiant et mot de passe de 10 caractères au moins).
2. Cette création n'est possible **qu'une seule fois**. Faites-la juste après la mise en ligne, avant de communiquer l'adresse du site.
3. Mot de passe perdu : supprimez le fichier `data/admin.json` depuis le gestionnaire de fichiers de l'hébergeur, puis recréez le compte.

## Guide d'utilisation

| Je veux… | Dans l'administration |
|---|---|
| Voir une demande de devis, rappeler le client | **Demandes de devis** : boutons Appeler, WhatsApp, e-mail, photos jointes |
| Ajouter un chantier | **Réalisations → Ajouter une réalisation** : titre, lieu, 3 à 8 photos, deux phrases sur le défi et la solution |
| Modifier un texte du site | **Textes du site** : choisir la langue et la rubrique, modifier, enregistrer |
| Remplacer un dessin par une vraie photo | **Photos du site** |
| Modifier les questions de la bulle « Questions rapides » | **Questions rapides** |
| Changer téléphone, horaires, e-mail, GPS | **Paramètres** |
| Remplacer la plaquette PDF | **Paramètres → Plaquette PDF** |

Les photos envoyées sont redimensionnées et allégées automatiquement. Avant de publier une photo, assurez-vous d'avoir l'accord du client et des personnes visibles.

## Mise en ligne chez Hostinger

1. Dans hPanel : créer le site sur le domaine, activer le certificat SSL, choisir PHP 8.1 ou plus récent.
2. Envoyer dans `public_html` tout le contenu du projet **sauf** `docs/`, `letsmanu-logos/`, `docker/`, `Dockerfile`, `docker-compose.yml`, `.dockerignore`, `router.php`, les fichiers `.md` et le contenu de `data/` (garder `data/.htaccess`).
3. Vérifier que les dossiers `data/` et `uploads/` sont inscriptibles (droits 755).
4. Ouvrir `https://votre-domaine/admin` et créer le compte.
5. Dans **Paramètres** : adresse officielle du site, e-mail qui reçoit les devis, e-mail d'expédition (une adresse du domaine, par exemple `no-reply@letsmanu.ci`, sinon les messages risquent d'arriver en indésirables), coordonnées GPS.
6. Envoyer une demande de devis de test avec une photo et vérifier que les deux e-mails arrivent.
7. Déclarer `https://votre-domaine/sitemap.xml` dans Google Search Console et créer la fiche Google Business Profile.

**Lors d'une mise à jour du site, ne jamais écraser `data/` ni `uploads/`** : ils contiennent les demandes, les textes modifiés et les photos.

Sauvegarde : ces deux mêmes dossiers suffisent. Hostinger sauvegarde le compte ; en garder aussi une copie hors du serveur chaque semaine.

## À valider par l'entreprise avant la mise en ligne

Le site est livré avec des contenus rédigés à partir du cahier des charges. Ces points ne peuvent être confirmés que par l'entreprise :

- **Réalisations** : les six fiches livrées sont des « projets types » qui décrivent des prestations courantes, pas de vrais chantiers. À remplacer (le tableau de bord le rappelle).
- **Photos** : tous les visuels sont des dessins techniques en attendant les vraies photos de chantiers, d'atelier et d'équipe.
- **Liste des prestations et du matériel** (pages des deux pôles) : à relire ligne par ligne.
- **Chiffres clés** de l'accueil, en particulier « 24 h » pour une urgence à Abidjan, et la mention « devis gratuit ».
- **Horaires** d'ouverture (valeurs provisoires) et numéro WhatsApp définitif.
- **Coordonnées GPS** exactes du siège.
- **Mot de la gérante** : la rubrique reste masquée tant que le texte n'est pas saisi (Textes du site → À propos).
- **Mentions légales** : adresse de l'hébergeur à vérifier lors de la souscription ; durée de conservation des données (3 ans) et déclaration à l'ARTCI à confirmer avec un juriste.
- **Logo** : le site utilise le logo officiel fourni dans `letsmanu-logos/` (fichiers PNG). Si une version vectorielle (SVG, AI ou PDF) existe chez le graphiste, la fournir : elle restera nette à toutes les tailles, y compris à l'impression.
- **Traduction anglaise** : à faire relire par un anglophone du métier.

## Documents

- `docs/` : cahier des charges.
- `CLAUDE.md` : architecture et conventions du code.
