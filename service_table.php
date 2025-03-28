<?php
session_start();
require 'config.php';
if (!isset($_SESSION['email']) || ($_SESSION['id_role'] != 1)) {
    // Rediriger vers la page de connexion si la session n'est pas définie
    header("Location: index.php");
    exit();
}

// Créer, Modifier, ou Supprimer des services
if (isset($_POST['add_service'])) {
    $service = trim($_POST['service_name']); // Supprime les espaces inutiles   
    
    // Vérifier si le service existe déjà
    $stmt = $conn->prepare("SELECT COUNT(*) FROM service WHERE libelle = ?");
    $stmt->bind_param("s", $service);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        // Si le service existe déjà, afficher un message d'erreur
        $_SESSION['message'] = "<p>Ce service existe déjà.</p>";
    } else {
        // Ajouter le service s'il n'existe pas encore
        $stmt = $conn->prepare("INSERT INTO service (libelle) VALUES (?)");
        $stmt->bind_param("s", $service);
        if ($stmt->execute()) {
            header("Location: service_table.php");
            exit();
        } else {
            $_SESSION['message'] = "<p>Erreur lors de l'ajout du service.</p>";
        }
        $stmt->close();
    }     
}


if (isset($_POST['edit_service'])) {
    $id_service = $_POST['id_service'];
    $service = $_POST['service_name'];
    $sql = "UPDATE service SET libelle='$service' WHERE id_service=$id_service";
    $conn->query($sql); 
    header("Location: service_table.php");
    exit();   
}

if (isset($_POST['delete_service'])) {
    $id_service = $_POST['id_service'];
    $sql = "DELETE FROM service WHERE id_service=$id_service";
    $conn->query($sql);
    header("Location: service_table.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Gestion des services</title>
    <link rel="stylesheet" href="styletable.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@1,300&display=swap" rel="stylesheet"/>
    <link rel="icon" href="Image/logo.png" type="image/x-icon">
    <link rel="shortcut icon" href="Image/logo.png" type="image/x-icon">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="content">
    <h1>Gestion des services</h1>

    <!-- Formulaire d'ajout -->
    <form method="POST" action="" class="form-add">
        <label for="service_name">Nom du service :</label>
        <input type="text" name="service_name" id="service_name" required>
        <button type="submit" name="add_service">Ajouter service</button> 
        <?php 
        if (isset($_SESSION['message'])) {
            echo $_SESSION['message'];
            // Supprimer le message de la session après l'avoir affiché
            unset($_SESSION['message']);
        }
        ?>       
    </form>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Service</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Récupérer les services depuis la base de données
                $sql = "SELECT * FROM service";
                $result = $conn->query($sql);

                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id_service'] . "</td>";
                    echo "<td>
                            <form method='POST'>
                                <input type='hidden' name='id_service' value='" . $row['id_service'] . "'>
                                <input type='text' name='service_name' value='" . $row['libelle'] . "' required>                            
                          </td>";
                    // Colonne "Modifier" avec l'icône dans un formulaire
                    echo "<td class='action-icons'>            
                                <button class='icon-button' type='submit' name='edit_service'>
                                    <img class='icon' src='Image/modifier.png' alt='Modifier'>
                                </button>
                          </td>";
                    // Colonne "Supprimer"
                    echo "<td class='action-icons'>                  
                                <button class='icon-button' type='submit' name='delete_service'>
                                    <img class='icon' src='Image/supprimer.png' alt='Supprimer'>
                                </button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php
    // Fermer la connexion après toute utilisation
    $conn->close();
    ?>
    </div>
</body>
</html>
