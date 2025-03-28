<?php
session_start();
require 'config.php';
if (!isset($_SESSION['email']) || ($_SESSION['id_role'] != 1 && $_SESSION['id_role'] != 2)) {
    // Rediriger vers la page de connexion si la session n'est pas définie
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Gestion des hospitalisations</title>
    <link rel="stylesheet" href="styletable.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@1,300&display=swap" rel="stylesheet"/>
    <link rel="icon" href="Image/logo.png" type="image/x-icon">
    <link rel="shortcut icon" href="Image/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <?php include 'sidebar.php'; ?> 
    <div class="content">
    <h1>Gestion des hospitalisations</h1>

    <?php
    if (isset($_POST['edit_hospitalisation'])) {
        $id_hospitalisation = $_POST['id_hospitalisation'];
        $pre_admission = $_POST['pre_admission'];
        $date_hospitalisation = $_POST['date_hospitalisation'];
        $heure_hospitalisation = $_POST['heure_hospitalisation'];
        $id_personnel = $_POST['id_personnel'];
        $id_chambre = $_POST['id_chambre'];

        $sql = "UPDATE hospitalisation SET pre_admission='$pre_admission', date_hospitalisation='$date_hospitalisation', heure_hospitalisation='$heure_hospitalisation', id_personnel='$id_personnel', id_chambre='$id_chambre' WHERE id_hospitalisation=$id_hospitalisation";
        $conn->query($sql);
        header("Location: hospitalisation_table.php");
        exit();
    }

    if (isset($_POST['delete_hospitalisation'])) {
        $id_hospitalisation = $_POST['id_hospitalisation'];
        $sql = "DELETE FROM hospitalisation WHERE id_hospitalisation=$id_hospitalisation";
        $conn->query($sql);
        header("Location: personnel_table.php");
        exit();
    }

    // Affichage des messages d'erreur, le cas échéant
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        // Supprimer le message de la session après l'avoir affiché
        unset($_SESSION['message']);
    }
    ?>


    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pré admission</th>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Patient</th>
                    <th>Personnel</th>
                    <th>Chambre</th>
                    <th>Utilisateur</th>   
                    <th>Modifier</th>
                    <th>Supprimer</th>                 
                </tr>
            </thead>
            <tbody>
                <?php
                // Récupérer les médecins depuis la base de données
                $sql_global = "SELECT hospitalisation.*, 
                      personnel.nom AS nom_utilisateur, 
                      patient.nom AS nom_patient 
               FROM hospitalisation
               LEFT JOIN personnel ON hospitalisation.id_utilisateur = personnel.id_personnel
               LEFT JOIN patient ON hospitalisation.id_patient = patient.id_patient";

                $result_global = $conn->query($sql_global);

                $sql_personnel = "SELECT id_personnel, nom FROM personnel";
                $result_personnel = $conn->query($sql_personnel);                
                $personnels = [];
                if ($result_personnel) {
                    while ($row_personnel = $result_personnel->fetch_assoc()) {
                        $personnels[] = $row_personnel;
                    }
                }  
                
                $sql_chambre = "SELECT id_chambre, type_chambre FROM chambre";
                $result_chambre = $conn->query($sql_chambre);                
                $chambres = [];
                if ($result_chambre) {
                    while ($row_chambre = $result_chambre->fetch_assoc()) {
                        $chambres[] = $row_chambre;
                    }
                }  

                while($row_global = $result_global->fetch_assoc()) {
                    echo "<tr>";
                    echo "<form method='POST'>";
                    echo "<td>" . $row_global['id_hospitalisation'] . "</td>";
                    $pre_admission = $row_global['pre_admission'];  
                    echo "<td>";
                    echo '<select name="pre_admission">';                   
                    echo '<option value="Ambulatoire chirurgie" ' . ($pre_admission == 'Ambulatoire chirurgie' ? 'selected' : '') . '>Ambulatoire chirurgie</option>';
                    echo '<option value="Hospitalisation" ' . ($pre_admission == 'Hospitalisation' ? 'selected' : '') . '>Hospitalisation (au moins une nuit)</option>';
                    echo '</select>';
                    echo "</td>";
                    echo "<td><input type='date' name='date_hospitalisation' min='" . date('Y-m-d') . "' max='2027-01-01' value='" . $row_global['date_hospitalisation'] . "' required></td>";
                    echo "<td><input type='text' name='heure_hospitalisation'id='heure_hospitalisation' value='" . $row_global['heure_hospitalisation'] . "' required></td>";
                    echo "<td>" . htmlspecialchars($row_global['nom_patient']) . "</td>";
                    echo "<td>";
                    echo "<select name='id_personnel' required>";
                    foreach ($personnels as $personnel) {
                        $selected = ($personnel['id_personnel'] == $row_global['id_personnel']) ? "selected" : "";
                        echo "<option value='" . $personnel['id_personnel'] . "' $selected>" . htmlspecialchars($personnel['nom']) . "</option>";
                    }
                    echo "</select>";
                    echo "</td>";     
                    echo "<td>";
                    echo "<select name='id_chambre' required>";
                    foreach ($chambres as $chambre) {
                        $selected = ($chambre['id_chambre'] == $row_global['id_chambre']) ? "selected" : "";
                        echo "<option value='" . $chambre['id_chambre'] . "' $selected>" . htmlspecialchars($chambre['type_chambre']) . "</option>";
                    }
                    echo "</select>";
                    echo "</td>";
                    echo "<td>" . htmlspecialchars($row_global['nom_utilisateur']) . "</td>";

                    // Colonne "Modifier" avec l'icône dans un formulaire
                    echo "<td class='action-icons'>
                                <input type='hidden' name='id_hospitalisation' value='" . $row_global['id_hospitalisation'] . "'>
                                <button class='icon-button' type='submit' name='edit_hospitalisation'>
                                    <img class='icon' src='Image/modifier.png' alt='Modifier'>
                                </button>
                          </td>";
                    // Colonne "Supprimer"
                    echo "<td class='action-icons'>
                                <button class='icon-button' type='submit' name='delete_hospitalisation'>
                                    <img class='icon' src='Image/supprimer.png' alt='Supprimer'>
                                </button>
                          </td>";
                    echo "</form>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    </div>
    <?php
    function validate_password($password) {
        // Expression régulière pour valider le mot de passe
        $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{12,}$/';
        return preg_match($pattern, $password);
    }

    // Fermer la connexion après toute utilisation
    $conn->close();
    ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="script.js"></script>      
</body>
</html>
