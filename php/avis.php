<?php
// Réception des avis des voyageurs. Endpoint public : entrées validées côté serveur.
// Un avis reçu n'est PAS visible : il attend la modération d'un admin (statut « en_attente »).
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
    repondre(405, ['erreur' => t('avis.err_method')]);
}

// Anti-spam : le champ « site_web » est invisible pour un humain. S'il est rempli, c'est un robot.
if (!empty($_POST['site_web'])) {
    repondre(200, ['ok' => true]);
}

$idVoyage = (int)($_POST['id_voyage'] ?? 0);
$nom = trim($_POST['nom'] ?? '');
$email = mb_strtolower(trim($_POST['email'] ?? ''));
$noteBrute = trim((string)($_POST['note'] ?? ''));
$commentaire = trim($_POST['commentaire'] ?? '');

if (mb_strlen($nom) < 2 || mb_strlen($nom) > 60) {
    repondre(422, ['erreur' => t('avis.err_name')]);
}
if (mb_strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    repondre(422, ['erreur' => t('avis.err_email')]);
}
if (!preg_match('/^[1-5]$/', $noteBrute)) {
    repondre(422, ['erreur' => t('avis.err_rating')]);
}
$note = (int)$noteBrute;
if (mb_strlen($commentaire) < 10 || mb_strlen($commentaire) > 1000) {
    repondre(422, ['erreur' => t('avis.err_comment')]);
}

try {
    // Le voyage doit exister
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM voyages WHERE id = ?');
    $stmt->execute([$idVoyage]);
    if ((int)$stmt->fetchColumn() === 0) {
        repondre(404, ['erreur' => t('avis.err_trip')]);
    }

    // Un seul avis par personne et par voyage (un avis refusé ne se renvoie pas non plus)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM avis WHERE email = ? AND id_voyage = ?');
    $stmt->execute([$email, $idVoyage]);
    if ((int)$stmt->fetchColumn() > 0) {
        repondre(409, ['erreur' => t('avis.err_duplicate')]);
    }

    // Limite : 3 avis par adresse courriel et par heure
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM avis WHERE email = ? AND created_at > (NOW() - INTERVAL 1 HOUR)');
    $stmt->execute([$email]);
    if ((int)$stmt->fetchColumn() >= 3) {
        repondre(429, ['erreur' => t('avis.err_rate')]);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO avis (id_voyage, nom, email, note, commentaire, langue) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$idVoyage, $nom, $email, $note, $commentaire, $langue]);
} catch (PDOException $e) {
    error_log('avis.php : ' . $e->getMessage());
    repondre(500, ['erreur' => t('avis.err_server')]);
}

repondre(201, ['ok' => true]);
