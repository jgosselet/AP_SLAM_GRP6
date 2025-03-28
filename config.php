<?php
$host = "localhost";  // Adresse du serveur MySQL (ou une IP si c'est distant)
$dbname = "cliniquelpfs";  // Nom de ta base de données
$username = "root";  // Ton utilisateur MySQL (modifie si nécessaire)
$password = "sio2024";  // Mot de passe de l'utilisateur MySQL (mettre le bon mot de passe)

// Connexion à MySQL
$conn = new mysqli($host, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Définir l'encodage des caractères (évite les problèmes avec accents et caractères spéciaux)
$conn->set_charset("utf8");
?>
