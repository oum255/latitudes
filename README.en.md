# Latitudes

*[Version française disponible ici](README.md)*

A **bilingual (French / English)** travel catalogue: a public site anyone can browse without an account, with a detail page and a quote request form for every destination, and an admin area to manage the content and follow up on requests.

Personal project built to explore end-to-end web app development in PHP/MySQL: authentication, content management with image uploads, internationalisation, search and filters.

## Features

**Public catalogue**
- Open browsing, no account needed: about thirty destinations across five continents, with image carousel
- Site in **French and English**: language detected from the browser, FR / EN switch, translated trip content
- **Free-text search** (title, country, theme, description), accent- and case-insensitive, multiple words supported
- Continent, country and category filters built from the trips actually present (no hard-coded list), plus sorting by price or duration
- Price, duration, availability and average rating shown on every card; pagination

**Trip page**
- Dedicated server-rendered page (`voyage.php?id=…`) with `hreflang` tags and sharing metadata (Open Graph)
- Photo gallery with a full-screen viewer and **photo credits** (author, licence)
- Quote request form (server-side validation, anti-spam protection, per-email rate limit)
- **Traveller reviews** (rating out of 5 and comment), published only after moderation; average rating and stars

**Admin area** (restricted to accounts with the `admin` role, in French)
- Add, edit and delete trips, with multi-image upload and a live preview
- Title and description in French and English; country picked from a list of 200+ countries (the continent is derived)
- Price, duration and available seats for every trip
- Inbox for quote requests (visitor language, mark as handled, delete), protected by a CSRF token
- Review moderation (publish, reject, withdraw) and newsletter subscriber list with CSV export

**Trust and compliance**
- **About**, **Legal notice** and **Privacy policy** pages, in French and English; the operator's identity is configured without touching the code
- **Newsletter** with explicit consent (unticked box), consent date kept and an unsubscribe link
- No advertising cookies or tracking tools; only a language cookie and, when signing in, a session cookie

**Authentication**
- Sign up and log in (passwords hashed with `password_hash` / verified with `password_verify`)
- Role management (`admin` / `utilisateur`) and hardened PHP sessions (session ID regenerated on login)

## Tech stack

- **Backend**: PHP 8 (PDO / MySQL, native sessions)
- **Database**: MySQL / MariaDB
- **Frontend**: HTML, CSS, vanilla JavaScript, [Bootstrap 5](https://getbootstrap.com/)

## Project structure

```
.
├── index.php                   # Public catalogue (no login)
├── voyage.php                  # Trip detail page + reviews + quote request
├── a-propos.php, mentions-legales.php, confidentialite.php   # Information pages
├── desabonnement.php           # Newsletter unsubscribe
├── login.php / register.php   # Auth pages
├── admin/
│   ├── dashboard.php            # Catalogue management (admin role required)
│   ├── demandes.php             # Quote requests inbox
│   ├── avis.php                 # Review moderation
│   └── abonnes.php              # Newsletter subscribers (CSV export)
├── includes/
│   ├── config.php               # Site name, languages, operator identity (overridable by config.local.php)
│   ├── i18n.php                 # Language detection, translations, formats (price, dates)
│   ├── referentiel.php          # Continents, categories, countries (with translations)
│   ├── pays.php                 # Country list: French name, ISO code, continent, English name
│   ├── lang/fr.php, en.php      # Site text in each language
│   └── …                        # Shared templates (navigation, filters, footer)
├── db.php                      # PDO database connection
├── php/
│   ├── auth.php                 # Register / login handling
│   ├── logout.php               # Logout
│   ├── voyages.php              # JSON API: public read (?lang=fr|en), admin-only write
│   ├── demande.php              # Quote request intake (validated public endpoint)
│   ├── avis.php                 # Review intake (held for moderation)
│   └── infolettre.php           # Newsletter sign-up (consent required)
├── js/
│   ├── commun.js                 # Translated texts, formats, search, filters and sorting
│   ├── catalogue.js              # Public catalogue (read-only, pagination)
│   ├── voyage.js                 # Trip page: gallery, viewer, quote and review forms
│   ├── infolettre.js             # Footer newsletter form
│   ├── main.js                   # Admin area (CRUD, pagination)
│   └── apercu.js                 # Live preview for the "add trip" form
├── css/                         # Stylesheets
├── data/credits_photos.json    # Credits for the freely licensed photos (shown on trip pages)
├── img/, videos/                # Static assets
├── uploads/                     # Trip photos
└── sql/
    ├── create_db.sql             # Database, users table (with roles) and demo admin account
    ├── voyages.sql               # Trips table with demo data
    ├── catalogue_enrichi.sql     # Price/duration/seats and quote requests table
    ├── destinations_bilingues.sql # English translations and extended catalogue (demo data)
    └── avis_et_infolettre.sql    # Reviews and newsletter subscribers tables
```

## Local setup

### Requirements

- PHP 8.1+ with the `pdo_mysql` and (recommended) `intl` extensions
- A MySQL/MariaDB server (e.g. via [MAMP](https://www.mamp.info/), XAMPP, or a local install)

### 1. Clone the repository

```bash
git clone <repo-url>
cd latitudes
```

### 2. Create the database

Run the five scripts in this order:

```bash
cat sql/create_db.sql sql/voyages.sql sql/catalogue_enrichi.sql sql/destinations_bilingues.sql sql/avis_et_infolettre.sql | mysql -u root -p
```

### 3. Configure the connection

By default, [`db.php`](db.php) uses `root` / `root` on `localhost` (matching a default MAMP install). Adjust these values to your environment.

### 4. Run the server

```bash
php -S localhost:8000
```

Open [http://localhost:8000](http://localhost:8000) for the public catalogue. The admin area is reached through [/login.php](http://localhost:8000/login.php) with the demo account created by `sql/create_db.sql`:

- Email: `admin@voyages.com`
- Password: `Demo-Latitudes-2026`

## Configuration

The site name, languages and the operator's identity (company name, address, travel agent permit number, person in charge of personal-data protection, host) are defined in [`includes/config.php`](includes/config.php). For a real deployment, create `includes/config.local.php` (ignored by git) and redefine the values there with `define(...)`. While `SITE_DEMO` is `true`, a notice states that prices, availability and dates are fictitious.

## Credits

The destination photos come from [Wikimedia Commons](https://commons.wikimedia.org/) under free licences: see [CREDITS.md](CREDITS.md).

## Author

Personal project — [Oumayma Haddour](https://github.com/oum255).
