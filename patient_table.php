<?php
session_start();
require 'config.php';
if (!isset($_SESSION['email']) || ($_SESSION['id_role'] != 1 && $_SESSION['id_role'] != 2)) {
    // Rediriger vers la page de connexion si la session n'est pas définie
    header("Location: index.php");
    exit();
}

if (isset($_POST['edit_patient'])) {
    $id_patient = $_POST['id_patient'];
    $nom = $_POST['nom'];
    $nom_epouse = $_POST['nom_epouse'];
    $prenom = $_POST['prenom'];
    $civilite = $_POST['civilite'];
    $date_naissance = $_POST['date_naissance'];
    $adresse = $_POST['adresse'];
    $cp = $_POST['cp'];
    $ville = $_POST['ville'];
    $email = $_POST['email'];
    $tel = $_POST['tel'];
    $numero_secu = $_POST['numero_secu'];
    $id_personne_prevenir = $_POST['id_personne_prevenir'];
    $id_personne_confiance = $_POST['id_personne_confiance'];
    $id_document = $_POST['id_document'];

    // Mise à jour des informations du patient
    $sql = "UPDATE patient SET nom='$nom', nom_epouse='$nom_epouse', prenom='$prenom', civilite='$civilite', date_naissance='$date_naissance', adresse='$adresse', cp='$cp', ville='$ville', email='$email', tel='$tel', numero_secu='$numero_secu', id_personne_prevenir='$id_personne_prevenir', id_personne_confiance='$id_personne_confiance', id_document='$id_document' WHERE id_patient=$id_patient";
    $conn->query($sql);
    header("Location: patient_table.php");
    exit();
}

if (isset($_POST['delete_patient'])) {
    $id_patient = $_POST['id_patient'];
    $sql = "DELETE FROM patient WHERE id_patient=$id_patient";
    $conn->query($sql);
    header("Location: patient_table.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Gestion des patients</title>
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
    <h1>Gestion des patients</h1>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Nom d'épouse</th>
                        <th>Prénom</th>
                        <th>Civilité</th>
                        <th>Date de naissance</th>
                        <th>Adresse</th>
                        <th>Code postal</th>
                        <th>Ville</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Numéro de sécurité sociale</th>
                        <th>Personne à prévenir</th>
                        <th>Personne de confiance</th>
                        <th>Document</th>
                        <th>Modifier</th>
                        <th>Supprimer</th>                 
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Récupérer les patients depuis la base de données
                    $sql_global = "SELECT * FROM patient";
                    $result_global = $conn->query($sql_global);

                    $sql_proche = "SELECT id_personne, nom AS nom_proche FROM proche";
                    $result_proche = $conn->query($sql_proche);                
                    $proches = [];
                    if ($result_proche) {
                        while ($row_proche = $result_proche->fetch_assoc()) {
                            $proches[] = $row_proche;
                        }
                    } 

                    while($row_global = $result_global->fetch_assoc()) {
                        echo "<tr>";
                        echo "<form method='POST'>";
                        echo "<td>" . $row_global['id_patient'] . "</td>";
                        echo "<td><input type='text' name='nom' value='" . $row_global['nom'] . "' required></td>";
                        echo "<td><input type='text' name='nom_epouse' value='" . $row_global['nom_epouse'] . "' ></td>";
                        echo "<td><input type='text' name='prenom' value='" . $row_global['prenom'] . "' required></td>";
                        echo "<td>
                        <select name='civilite'>
                            <option value='Homme' " . ($row_global['civilite'] == 'Homme' ? 'selected' : '') . ">Homme</option>
                            <option value='Femme' " . ($row_global['civilite'] == 'Femme' ? 'selected' : '') . ">Femme</option>
                        </select>
                        </td>";            
                        echo "<td><input type='date' name='date_naissance' value='" . $row_global['date_naissance'] . "' required></td>";
                        echo "<td><input type='text' name='adresse' value='" . $row_global['adresse'] . "' required></td>";
                        echo "<td><input type='text' name='cp' value='" . $row_global['cp'] . "' minlength='5' maxlength='5' pattern='[0-9]*' required></td>";
                        echo "<td><input type='text' name='ville' value='" . $row_global['ville'] . "' required></td>";
                        echo "<td><input type='email' name='email' value='" . $row_global['email'] . "' required></td>";
                        echo "<td><input type='tel' name='tel' value='" . $row_global['tel'] . "' minlength='10' maxlength='10' pattern='[0-9]*' required></td>";
                        echo "<td><input type='text' name='numero_secu' value='" . $row_global['numero_secu'] . "' minlength='15' maxlength='15' pattern='[0-9]*' required></td>";
                        echo "<td>";
                        echo "<select name='id_personne_prevenir' required>";
                        foreach ($proches as $proche) {
                            $selected = ($proche['id_personne'] == $row_global['id_personne_prevenir']) ? "selected" : "";
                            echo "<option value='" . $proche['id_personne'] . "' $selected>" . htmlspecialchars($proche['nom_proche']) . "</option>";
                        }
                        echo "</select>";
                        echo "</td>";     
                        echo "<td>";
                        echo "<select name='id_personne_confiance' required>";
                        foreach ($proches as $proche) {
                            $selected = ($proche['id_personne'] == $row_global['id_personne_confiance']) ? "selected" : "";
                            echo "<option value='" . $proche['id_personne'] . "' $selected>" . htmlspecialchars($proche['nom_proche']) . "</option>";
                        }
                        echo "</select>";
                        echo "</td>";     
                        echo "<td><input type='number' name='id_document' value='" . $row_global['id_document'] . "' required></td>";

                        // Colonne "Modifier" avec l'icône dans un formulaire
                        echo "<td class='action-icons'>
                                    <input type='hidden' name='id_patient' value='" . $row_global['id_patient'] . "'>
                                    <button class='icon-button' type='submit' name='edit_patient'>
                                        <img class='icon' src='Image/modifier.png' alt='Modifier'>
                                    </button>
                            </td>";
                        // Colonne "Supprimer"
                        echo "<td class='action-icons'>
                                    <button class='icon-button' type='submit' name='delete_patient'>
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
        <?php
        // Affichage des messages d'erreur, le cas échéant
        if (isset($_SESSION['message'])) {
            echo $_SESSION['message'];
            // Supprimer le message de la session après l'avoir affiché
            unset($_SESSION['message']);
        }
        ?>
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
</body>
</html>
