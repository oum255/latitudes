<?php
// Affichage des erreurs activé pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
require_once(__DIR__ . '/../db.php');
session_start();

/* Traitement de l’inscription */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // Vérifie si l’adresse email est déjà utilisée
    $verif = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
    $verif->execute([$email]);
    if ($verif->fetchColumn() > 0) {
        header("Location: ../register.php?erreur=existe");
        exit();
    }

    // Hachage sécurisé du mot de passe
    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    // Insertion du nouvel utilisateur
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$nom, $email, $mot_de_passe_hash]);
        header("Location: ../login.php?success=inscription");
        exit();
    } catch (PDOException $e) {
        // En cas d'erreur SQL
        die("Erreur d'inscription : " . $e->getMessage());
    }
}

/* Traitement de la connexion */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // Recherche de l’utilisateur par email
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification du mot de passe
    if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        // Régénère l'identifiant de session (évite la fixation de session)
        session_regenerate_id(true);

        // Création de la session utilisateur
        $_SESSION['id'] = $utilisateur['id'];
        $_SESSION['nom'] = $utilisateur['nom'];
        $_SESSION['role'] = $utilisateur['role'];

        // Seul un compte admin accède à l'espace de gestion
        if ($utilisateur['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit();
    } else {
        // Redirection en cas d’échec
        header("Location: ../login.php?erreur=identifiants");
        exit();
    }
}
?>