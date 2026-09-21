# Latitudes

*[English version available here](README.en.md)*

Catalogue de voyages **bilingue (français / anglais)** : un site public consultable sans compte, avec fiche détaillée et demande de devis pour chaque destination, et un espace d'administration pour gérer le contenu et suivre les demandes.

Projet personnel développé pour explorer la création d'une application web complète en PHP/MySQL : authentification, gestion de contenu avec upload d'images, internationalisation, recherche et filtres.

## Fonctionnalités

**Catalogue public**
- Consultation libre, sans compte : une trentaine de destinations réparties sur cinq continents, avec carrousel d'images
- Site en **français et en anglais** : langue détectée d'après le navigateur, sélecteur FR / EN, contenu des voyages traduit
- **Recherche libre** (titre, pays, thème, description) insensible aux accents et aux majuscules, plusieurs mots possibles
- Filtres par continent, pays et catégorie construits à partir des voyages réellement présents (aucune liste figée), et tri par prix ou par durée
- Prix, durée, disponibilité et note moyenne affichés sur chaque carte ; pagination

**Fiche d'un voyage**
- Page dédiée rendue côté serveur (`voyage.php?id=…`) avec balises `hreflang` et métadonnées de partage (Open Graph)
- Galerie photo avec visionneuse plein écran et **crédits des photos** (auteur, licence)
- Formulaire de demande de devis (validation côté serveur, protection anti-spam, limite par adresse courriel)
- **Avis des voyageurs** (note sur 5 et commentaire), publiés seulement après modération ; note moyenne et étoiles

**Espace admin** (réservé aux comptes avec le rôle `admin`, en français)
- Ajout, modification et suppression de voyages, avec upload de plusieurs images et aperçu en temps réel
- Titre et description en français et en anglais ; pays choisi dans une liste de plus de 200 pays (le continent est déduit)
- Prix, durée et places disponibles pour chaque voyage
- Boîte de réception des demandes de devis (langue du visiteur, marquer comme traitée, supprimer), protégée par jeton CSRF
- Modération des avis (publier, refuser, retirer) et liste des abonnés à l'infolettre avec export CSV

**Confiance et conformité**
- Pages **À propos**, **Mentions légales** et **Politique de confidentialité**, en français et en anglais ; l'identité de l'exploitant se configure sans toucher au code
- **Infolettre** avec consentement explicite (case décochée par défaut), date du consentement conservée et lien de désabonnement
- Aucun témoin publicitaire ni outil de suivi ; seuls un témoin de langue et, à la connexion, un témoin de session

**Authentification**
- Inscription et connexion (mots de passe hachés avec `password_hash` / vérifiés avec `password_verify`)
- Gestion des rôles (`admin` / `utilisateur`) et sessions PHP sécurisées (régénération de l'identifiant à la connexion)

## Stack technique

- **Back-end** : PHP 8 (PDO / MySQL, sessions natives)
- **Base de données** : MySQL / MariaDB
- **Front-end** : HTML, CSS, JavaScript vanilla, [Bootstrap 5](https://getbootstrap.com/)

## Structure du projet

```
.
├── index.php                   # Catalogue public (sans connexion)
├── voyage.php                  # Fiche détaillée d'un voyage + avis + demande de devis
├── a-propos.php, mentions-legales.php, confidentialite.php   # Pages d'information
├── desabonnement.php           # Désabonnement de l'infolettre
├── login.php / register.php   # Pages d'authentification
├── admin/
│   ├── dashboard.php            # Espace de gestion du catalogue (rôle admin requis)
│   ├── demandes.php             # Boîte de réception des demandes de devis
│   ├── avis.php                 # Modération des avis
│   └── abonnes.php              # Abonnés à l'infolettre (export CSV)
├── includes/
│   ├── config.php               # Nom du site, langues, identité de l'exploitant (surchargeable par config.local.php)
│   ├── i18n.php                 # Détection de la langue, traductions, formats (prix, dates)
│   ├── referentiel.php          # Continents, catégories, pays (avec traductions)
│   ├── pays.php                 # Liste des pays : nom français, code ISO, continent, nom anglais
│   ├── lang/fr.php, en.php      # Textes du site dans chaque langue
│   └── …                        # Gabarits partagés (navigation, filtres, pied de page)
├── db.php                      # Connexion PDO à la base de données
├── php/
│   ├── auth.php                 # Traitement inscription / connexion
│   ├── logout.php               # Déconnexion
│   ├── voyages.php              # API JSON : lecture publique (?lang=fr|en), écriture réservée aux admins
│   ├── demande.php              # Réception des demandes de devis (endpoint public validé)
│   ├── avis.php                 # Réception des avis (mis en attente de modération)
│   └── infolettre.php           # Inscription à l'infolettre (consentement obligatoire)
├── js/
│   ├── commun.js                 # Textes traduits, formats, recherche, filtres et tri
│   ├── catalogue.js              # Catalogue public (lecture seule, pagination)
│   ├── voyage.js                 # Fiche voyage : galerie, visionneuse, formulaires de devis et d'avis
│   ├── infolettre.js             # Formulaire d'infolettre du pied de page
│   ├── main.js                   # Espace admin (CRUD, pagination)
│   └── apercu.js                 # Aperçu dynamique du formulaire d'ajout
├── css/                         # Feuilles de style
├── data/credits_photos.json    # Crédits des photos libres de droits (affichés sur les fiches)
├── img/, videos/                # Assets statiques
├── uploads/                     # Photos des voyages
└── sql/
    ├── create_db.sql             # Base, table utilisateurs (avec rôles) et compte admin de démo
    ├── voyages.sql               # Table des voyages avec données de démo
    ├── catalogue_enrichi.sql     # Prix/durée/places et table des demandes de devis
    ├── destinations_bilingues.sql # Traductions anglaises et catalogue élargi (données de démo)
    └── avis_et_infolettre.sql    # Tables des avis et des abonnés à l'infolettre
```

## Installation locale

### Prérequis

- PHP 8.1+ avec les extensions `pdo_mysql` et (recommandée) `intl`
- Un serveur MySQL/MariaDB (ex. via [MAMP](https://www.mamp.info/), XAMPP, ou une installation locale)

### 1. Cloner le dépôt

```bash
git clone <url-du-depot>
cd latitudes
```

### 2. Créer la base de données

Les cinq scripts doivent être exécutés dans cet ordre :

```bash
cat sql/create_db.sql sql/voyages.sql sql/catalogue_enrichi.sql sql/destinations_bilingues.sql sql/avis_et_infolettre.sql | mysql -u root -p
```

### 3. Configurer la connexion

Par défaut, [`db.php`](db.php) utilise `root` / `root` sur `localhost` (adapté à une installation MAMP par défaut). Adaptez ces valeurs à votre environnement.

### 4. Lancer le serveur

```bash
php -S localhost:8000
```

Ouvrez [http://localhost:8000](http://localhost:8000) pour le catalogue public. L'espace admin est accessible via [/login.php](http://localhost:8000/login.php) avec le compte de démonstration créé par `sql/create_db.sql` :

- Courriel : `admin@voyages.com`
- Mot de passe : `Demo-Latitudes-2026`

## Configuration

Le nom du site, les langues et l'identité de l'exploitant (raison sociale, adresse, numéro de permis d'agent de voyages, responsable de la protection des renseignements personnels, hébergeur) sont définis dans [`includes/config.php`](includes/config.php). Pour un déploiement réel, créez `includes/config.local.php` (ignoré par git) et redéfinissez-y les valeurs avec `define(...)`. Tant que `SITE_DEMO` vaut `true`, un avis précise que les prix, disponibilités et dates sont fictifs.

## Crédits

Les photos des destinations viennent de [Wikimedia Commons](https://commons.wikimedia.org/) sous licences libres : voir [CREDITS.md](CREDITS.md).

## Auteur·rice

Projet personnel — [Oumayma Haddour](https://github.com/oum255).
