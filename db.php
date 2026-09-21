<?php
$host = 'localhost';
$dbname = 'latitudes';
$user = 'root';
$pass = 'root'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Les horodatages sont enregistrés et lus en UTC ; l'affichage les convertit à l'heure locale du site.
    $pdo->exec("SET time_zone = '+00:00'");
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
