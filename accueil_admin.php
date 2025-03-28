<?php
session_start();
if (!isset($_SESSION['email']) || ($_SESSION['id_role'] != 1)) {
    // Rediriger vers la page de connexion si la session n'est pas définie
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <title>Tableau de Bord Admin</title>
    <link rel="stylesheet" href="styleadmin.css" />
</head>
<?php include 'sidebar.php'; ?>
<div class="content">
</div>  
</body>
</html>