<?php
session_start();
require 'config.php';
if (!isset($_SESSION['email']) || ($_SESSION['id_role'] != 1)) {
    // Rediriger vers la page de connexion si la session n'est pas définie
    header("Location: index.php");
    exit();
}

// Créer, Modifier, ou Supprimer des services
if (isset($_POST['add_metier'])) {
    $id_service = $_POST['id_service'] ;
    $metier = trim($_POST['metier_name']); // Supprime les espaces inutiles  
        
    // Vérifier si le service existe déjà
    $stmt = $conn->prepare("SELECT COUNT(*) FROM metier WHERE libelle = ?");
    $stmt->bind_param("s", $metier);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        // Si le service existe déjà, afficher un message d'erreur
        $_SESSION['message'] = "<p>Ce métier existe déjà.</p>";
    } else {
        // Ajouter le service s'il n'existe pas encore
        $stmt = $conn->prepare("INSERT INTO metier (libelle, id_service) VALUES (?, ?)");
        $stmt->bind_param("si", $metier, $id_service);
        if ($stmt->execute()) {
            header("Location: metier_table.php");
            exit();
        } else {
            $_SESSION['message'] = "<p>Erreur lors de l'ajout du service.</p>";
        }
        $stmt->close();
    }     
}


if (isset($_POST['edit_metier'])) {
    $id_metier = $_POST['id_metier'];
    $id_service = $_POST['id_service'];
    $metier = $_POST['metier_name'];
    $sql = "UPDATE metier SET libelle=?, id_service=? WHERE id_metier=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $metier, $id_service, $id_metier);
    $stmt->execute();
    $stmt->close(); 
    header("Location: metier_table.php");
    exit();   
}

if (isset($_POST['delete_metier'])) {
    $id_metier = $_POST['id_metier'];
    // Vérifier si des personnels sont associés à ce service
    $stmt = $conn->prepare("SELECT COUNT(*) FROM personnel p  WHERE p.id_metier = ?");
    $stmt->bind_param("i", $id_metier);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    if ($count > 0) {
        $_SESSION['message'] = "<p>Impossible de supprimer ce métier car des personnels y sont rattachés.</p>";
        
    } else {
        $sql = "DELETE FROM metier WHERE id_metier=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_metier);
        if ($stmt->execute()) {
            header("Location: metier_table.php");
            exit();
        }
        $stmt->close();
    }
}

$sql_service = "SELECT id_service, libelle FROM service";
$result_service = $conn->query($sql_service);

$services= [];
if ($result_service) {
    while ($row_service = $result_service->fetch_assoc()) {
        $services[] = $row_service;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Gestion des métiers</title>
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
    <h1>Gestion des métiers</h1>

    <!-- Formulaire d'ajout -->
    <form method="POST" action="" class="form-add">
        <div class="ligne">
            <div class="gauche">
                <label for="metier_name">Nom du métier :</label>
                <input type="text" name="metier_name" id="metier_name" required>   
            </div>
            <div class="droite">
                <label for="id_service">Service :</label>
                <select name="id_service" id="id_service" style="width: 100%;" required>
                    <option value="" disabled selected>Choix</option>
                    <?php
                    foreach ($services as $service) {
                        echo "<option value='" . $service['id_service'] . "'>" . $service['libelle'] . "</option>";
                    }
                    ?>
                </select>
            </div>           
        </div>       
        <button type="submit" name="add_metier">Ajouter métier</button> 
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
                    <th>Métier</th>
                    <th>Service</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Récupérer les services depuis la base de données
                $sql = "SELECT * FROM metier";
                $result = $conn->query($sql);

                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id_metier'] . "</td>";
                    echo "<td>
                            <form method='POST'>
                                <input type='hidden' name='id_metier' value='" . $row['id_metier'] . "'>
                                <input type='text' name='metier_name' value='" . $row['libelle'] . "' required>                            
                          </td>";
                    echo "<td>";
                    echo "<select name='id_service' required>";
                    foreach ($services as $service) {
                        $selected = ($service['id_service'] == $row['id_service']) ? "selected" : "";
                        echo "<option value='" . $service['id_service'] . "' $selected>" . htmlspecialchars($service['libelle']) . "</option>";
                    }
                    echo "</select>";
                    echo "</td>";
                         
                    // Colonne "Modifier" avec l'icône dans un formulaire
                    echo "<td class='action-icons'>            
                                <button class='icon-button' type='submit' name='edit_metier'>
                                    <img class='icon' src='Image/modifier.png' alt='Modifier'>
                                </button>
                          </td>";
                    // Colonne "Supprimer"
                    echo "<td class='action-icons'>                  
                                <button class='icon-button' type='submit' name='delete_metier'>
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
