<?php
// Inscription à l'infolettre. Endpoint public : entrées validées côté serveur, consentement explicite obligatoire.
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
    repondre(405, ['erreur' => t('newsletter.err_method')]);
}

// Anti-spam : champ « site_web » invisible pour un humain. S'il est rempli, c'est un robot.
if (!empty($_POST['site_web'])) {
    repondre(200, ['ok' => true]);
}

$email = mb_strtolower(trim($_POST['email'] ?? ''));

if (mb_strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    repondre(422, ['erreur' => t('newsletter.err_email')]);
}
// Consentement explicite (case décochée par défaut) : sans lui, aucune inscription.
if (($_POST['consentement'] ?? '') !== '1') {
    repondre(422, ['erreur' => t('newsletter.err_consent')]);
}

try {
    // Nouvel abonné, ou réabonnement d'une personne qui s'était désabonnée (nouveau consentement daté).
    // Si l'adresse est déjà abonnée, rien ne change ; la réponse est la même dans tous les cas
    // (pour ne pas révéler quelles adresses sont inscrites).
    $stmt = $pdo->prepare(
        'INSERT INTO abonnes (email, langue, token) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE
            consenti_le = IF(desabonne_le IS NULL, consenti_le, CURRENT_TIMESTAMP),
            langue = IF(desabonne_le IS NULL, langue, VALUES(langue)),
            desabonne_le = NULL'
    );
    $stmt->execute([$email, $langue, bin2hex(random_bytes(16))]);
} catch (PDOException $e) {
    error_log('infolettre.php : ' . $e->getMessage());
    repondre(500, ['erreur' => t('newsletter.err_server')]);
}

repondre(201, ['ok' => true]);
