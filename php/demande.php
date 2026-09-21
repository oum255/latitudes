<?php
// Réception des demandes de devis envoyées depuis la fiche d'un voyage.
// Endpoint public : toutes les entrées sont validées côté serveur.
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/i18n.php';
header('Content-Type: application/json; charset=utf-8');
$langue = langueCourante(false); // langue du visiteur (champ « lang » du formulaire)

function repondre(int $code, array $donnees): void
{
    http_response_code($code);
    echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondre(405, ['erreur' => t('devis.err_method')]);
}

// Anti-spam : le champ « site_web » est invisible pour un humain. S'il est rempli,
// c'est un robot : on répond « ok » sans rien enregistrer.
if (!empty($_POST['site_web'])) {
    repondre(200, ['ok' => true]);
}

$idVoyage = (int)($_POST['id_voyage'] ?? 0);
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$nbVoyageurs = (int)($_POST['nb_voyageurs'] ?? 0);
$message = trim($_POST['message'] ?? '');

if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
    repondre(422, ['erreur' => t('devis.err_name')]);
}
if (mb_strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    repondre(422, ['erreur' => t('devis.err_email')]);
}
if ($telephone !== '' && !preg_match('/^[0-9 +().\-]{7,30}$/', $telephone)) {
    repondre(422, ['erreur' => t('devis.err_phone')]);
}
if ($nbVoyageurs < 1 || $nbVoyageurs > 20) {
    repondre(422, ['erreur' => t('devis.err_travellers')]);
}
if (mb_strlen($message) > 2000) {
    repondre(422, ['erreur' => t('devis.err_message')]);
}

try {
    // Le voyage doit exister
    $stmt = $pdo->prepare('SELECT titre FROM voyages WHERE id = ?');
    $stmt->execute([$idVoyage]);
    $titreVoyage = $stmt->fetchColumn();
    if ($titreVoyage === false) {
        repondre(404, ['erreur' => t('devis.err_trip')]);
    }

    // Limite : 3 demandes par adresse courriel et par heure
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM demandes WHERE email = ? AND created_at > (NOW() - INTERVAL 1 HOUR)');
    $stmt->execute([$email]);
    if ((int)$stmt->fetchColumn() >= 3) {
        repondre(429, ['erreur' => t('devis.err_rate')]);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO demandes (id_voyage, titre_voyage, nom, email, telephone, nb_voyageurs, message, langue)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $idVoyage,
        $titreVoyage,
        $nom,
        $email,
        $telephone !== '' ? $telephone : null,
        $nbVoyageurs,
        $message !== '' ? $message : null,
        $langue,
    ]);
} catch (PDOException $e) {
    error_log('demande.php : ' . $e->getMessage());
    repondre(500, ['erreur' => t('devis.err_server')]);
}

repondre(201, ['ok' => true]);
