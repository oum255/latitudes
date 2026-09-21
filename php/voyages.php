<?php
// La session n'est ouverte que si le navigateur en possède déjà une (l'admin connecté) :
// un simple visiteur qui consulte le catalogue ne reçoit aucun cookie de session.
if (isset($_COOKIE[session_name()])) {
    session_start();
}
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/outils.php';
header('Content-Type: application/json');

// Lecture publique : n'importe quel visiteur peut consulter le catalogue.
// Écriture réservée aux comptes admin (voir plus bas, avant le traitement des POST).
$estAdmin = isset($_SESSION['id']) && ($_SESSION['role'] ?? '') === 'admin';
$id_utilisateur = $_SESSION['id'] ?? null;

/* Interrompt la requête avec une erreur 422 (message en français : seul l'espace admin écrit). */
function refuser(string $message): void
{
    http_response_code(422);
    echo json_encode(['erreur' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

/* Lit et valide les champs commerciaux (prix, durée, places), tous facultatifs.
   Un champ vide devient NULL ; une valeur invalide interrompt la requête (422). */
function champsCommerciaux(): array
{
    $lire = function (string $cle) {
        $v = trim((string)($_POST[$cle] ?? ''));
        return $v === '' ? null : str_replace(',', '.', $v);
    };

    $prix = $lire('prix');
    $duree = $lire('duree_jours');
    $places = $lire('places_disponibles');

    $valide =
        ($prix === null || (preg_match('/^\d{1,8}(\.\d{1,2})?$/', $prix))) &&
        ($duree === null || (preg_match('/^\d{1,3}$/', $duree) && (int)$duree >= 1 && (int)$duree <= 365)) &&
        ($places === null || (preg_match('/^\d{1,4}$/', $places)));

    if (!$valide) {
        refuser('Prix, durée (1 à 365 jours) ou nombre de places invalide.');
    }

    return [
        $prix,
        $duree === null ? null : (int)$duree,
        $places === null ? null : (int)$places,
    ];
}

/* Lit et valide les champs d'un voyage (ajout ou modification complète). */
function champsVoyage(): array
{
    $titre = trim((string)($_POST['titre'] ?? ''));
    $titreEn = trim((string)($_POST['titre_en'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $descriptionEn = trim((string)($_POST['description_en'] ?? ''));
    $date = trim((string)($_POST['date_depart'] ?? ''));
    $pays = (string)($_POST['pays'] ?? '');
    $categorie = (string)($_POST['categorie'] ?? '');

    if ($titre === '' || mb_strlen($titre) > 255) {
        refuser('Le titre est obligatoire (255 caractères maximum).');
    }
    if (mb_strlen($titreEn) > 255) {
        refuser('Le titre anglais est trop long (255 caractères maximum).');
    }
    if ($description === '' || mb_strlen($description) > 5000) {
        refuser('La description est obligatoire (5 000 caractères maximum).');
    }
    if (mb_strlen($descriptionEn) > 5000) {
        refuser('La description anglaise est trop longue (5 000 caractères maximum).');
    }
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        refuser('La date de départ est invalide.');
    }
    if (!paysValide($pays)) {
        refuser('Veuillez choisir un pays dans la liste.');
    }
    if (!categorieValide($categorie)) {
        refuser('Veuillez choisir une catégorie dans la liste.');
    }

    [$prix, $duree, $places] = champsCommerciaux();

    return [
        'titre' => $titre,
        'titre_en' => $titreEn !== '' ? $titreEn : null,
        'description' => $description,
        'description_en' => $descriptionEn !== '' ? $descriptionEn : null,
        'date_depart' => $date,
        'pays' => $pays,
        'continent' => continentDuPays($pays), // déduit du pays : ne peut pas être incohérent
        'categorie' => $categorie,
        'prix' => $prix,
        'duree_jours' => $duree,
        'places_disponibles' => $places,
    ];
}

/* Enregistre les fichiers téléversés (champ $champ) et retourne leurs chemins « uploads/... ». */
function enregistrerImages(string $champ): array
{
    $chemins = [];
    if (!empty($_FILES[$champ]) && is_array($_FILES[$champ]['name'])) {
        foreach ($_FILES[$champ]['name'] as $index => $name) {
            if ($_FILES[$champ]['error'][$index] === 0) {
                $nomFichier = uniqid() . '_' . basename($name);
                $dossier = '../uploads/';
                if (!is_dir($dossier)) mkdir($dossier);
                move_uploaded_file($_FILES[$champ]['tmp_name'][$index], $dossier . $nomFichier);
                $chemins[] = 'uploads/' . $nomFichier;
            }
        }
    }
    return $chemins;
}

// Traitement des requêtes POST (ajout / modification / suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!$estAdmin) {
        http_response_code(403);
        echo json_encode(['erreur' => 'Non autorisé']);
        exit;
    }

    // Suppression d’un voyage (et des images associées)
    if (isset($_POST['supprimer'])) {
        $id = (int)$_POST['supprimer'];

        // Récupère les images du voyage à supprimer
        $stmt = $pdo->prepare("SELECT image FROM voyages WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Supprime physiquement les fichiers image
        if ($result && $result['image']) {
            $images = json_decode($result['image'], true);
            if (is_array($images)) {
                foreach ($images as $image) {
                    $chemin = '../' . $image;
                    if (file_exists($chemin)) {
                        unlink($chemin);
                    }
                }
            }
        }

        // Supprime l’entrée dans la base de données
        $stmt = $pdo->prepare("DELETE FROM voyages WHERE id = ?");
        $stmt->execute([$id]);
        exit;
    }

    // Modification simple (seulement le titre)
    if (isset($_POST['modifier']) && isset($_POST['titre'])) {
        $id = (int)$_POST['modifier'];
        $titre = trim($_POST['titre']);
        $stmt = $pdo->prepare("UPDATE voyages SET titre = ? WHERE id = ?");
        $stmt->execute([$titre, $id]);
        exit;
    }

    // Modification complète d’un voyage
    if (isset($_POST['modifier_complet'])) {
        $id = (int)$_POST['modifier_complet'];
        $v = champsVoyage();

        // Récupère les images conservées par l’utilisateur
        $imagesRestantes = [];
        if (!empty($_POST['images_restantes'])) {
            $imagesRestantes = json_decode($_POST['images_restantes'], true);
            if (!is_array($imagesRestantes)) {
                $imagesRestantes = [];
            }
        }

        // Supprime les anciennes images non conservées
        $stmt = $pdo->prepare("SELECT image FROM voyages WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['image']) {
            $imagesAvant = json_decode($result['image'], true);
            if (is_array($imagesAvant)) {
                $imagesASupprimer = array_diff($imagesAvant, $imagesRestantes);
                foreach ($imagesASupprimer as $image) {
                    $chemin = '../' . $image;
                    if (file_exists($chemin)) unlink($chemin);
                }
            }
        }

        // Enregistre les nouvelles images uploadées
        $imagesRestantes = array_merge($imagesRestantes, enregistrerImages('new_images'));

        // Met à jour le voyage dans la base
        $stmt = $pdo->prepare(
            "UPDATE voyages SET titre = ?, titre_en = ?, date_depart = ?, continent = ?, pays = ?, categorie = ?,
                    description = ?, description_en = ?, prix = ?, duree_jours = ?, places_disponibles = ?, image = ?
             WHERE id = ?"
        );
        $stmt->execute([
            $v['titre'], $v['titre_en'], $v['date_depart'], $v['continent'], $v['pays'], $v['categorie'],
            $v['description'], $v['description_en'], $v['prix'], $v['duree_jours'], $v['places_disponibles'],
            json_encode($imagesRestantes), $id,
        ]);
        exit;
    }

    // Ajout d’un nouveau voyage
    if (isset($_POST['titre'], $_POST['description'])) {
        $v = champsVoyage();
        $imagePaths = enregistrerImages('images');

        $stmt = $pdo->prepare(
            "INSERT INTO voyages (titre, titre_en, date_depart, continent, pays, categorie, description, description_en,
                                  prix, duree_jours, places_disponibles, image, id_utilisateur)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $v['titre'], $v['titre_en'], $v['date_depart'], $v['continent'], $v['pays'], $v['categorie'],
            $v['description'], $v['description_en'], $v['prix'], $v['duree_jours'], $v['places_disponibles'],
            json_encode($imagePaths), $id_utilisateur,
        ]);
        exit;
    }

    // Aucune action POST reconnue
    echo json_encode(['status' => 'aucune_action_post']);
    exit;
}

// Récupération du catalogue complet (GET, public — pas de filtre par utilisateur).
// Avec ?lang=fr|en, chaque voyage reçoit aussi ses valeurs prêtes à afficher (champs « *_affiche »).
// Chaque voyage porte aussi le nombre d'avis publiés et leur note moyenne (null sans avis).
$lignes = $pdo->query(
    "SELECT v.*, a.nb_avis, a.note_moyenne
       FROM voyages v
       LEFT JOIN (SELECT id_voyage, COUNT(*) AS nb_avis, ROUND(AVG(note), 1) AS note_moyenne
                    FROM avis WHERE statut = 'publie' GROUP BY id_voyage) a ON a.id_voyage = v.id
      ORDER BY v.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);
if (isset($_GET['lang'])) {
    $langue = langueCourante(false);
    $lignes = array_map(fn($ligne) => voyageAffichage($ligne, $langue), $lignes);
}
echo json_encode($lignes);
