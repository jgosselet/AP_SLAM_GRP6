<?php
session_start();
require 'config.php';
if (!isset($_SESSION['email']) || ($_SESSION['id_role'] != 1)) {
    // Rediriger vers la page de connexion si la session n'est pas définie
    header("Location: index.php");
    exit();
}

// Ajouter, Modifier, ou Supprimer des médecins
if (isset($_POST['add_personnel'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);
    $mot_de_passe_clair = $_POST['mot_de_passe'];
    $id_metier = $_POST['id_metier'];
    if (!validate_password($_POST['mot_de_passe'])) {
        $_SESSION['message'] = "<p>Le mot de passe doit contenir au moins 12 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.</p>";
        header("Location: personnel_table.php");
        exit();
    } else {    
        $sql = "INSERT INTO personnel (nom, prenom, email, mot_de_passe, mot_de_passe_clair, id_role, id_metier) VALUES ('$nom', '$prenom', '$email', '$mot_de_passe', '$mot_de_passe_clair',  3, '$id_metier')";
        $conn->query($sql);
        // Redirection pour éviter le renvoi de données
        header("Location: personnel_table.php");
        exit();
    }    
}

if (isset($_POST['edit_personnel'])) {
    $id_personnel = $_POST['id_personnel'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $mot_de_passe_clair = $_POST['mot_de_passe_clair'];
    $id_metier = $_POST['id_metier'];

    $sql = "UPDATE personnel SET nom='$nom', prenom='$prenom', email='$email', mot_de_passe='$mot_de_passe', mot_de_passe_clair='$mot_de_passe_clair', id_metier='$id_metier' WHERE id_personnel=$id_personnel";
    $conn->query($sql);
    header("Location: personnel_table.php");
    exit();
}

if (isset($_POST['delete_personnel'])) {
    $id_personnel = $_POST['id_personnel'];
    $sql = "DELETE FROM personnel WHERE id_personnel=$id_personnel";
    $conn->query($sql);
    header("Location: personnel_table.php");
    exit();
}


$sql_metier = "SELECT id_metier, libelle FROM metier";
$result_metier = $conn->query($sql_metier);

$metiers= [];
if ($result_metier) {
    while ($row_metier = $result_metier->fetch_assoc()) {
        $metiers[] = $row_metier;
    }
}

$sql_role = "SELECT id_role, libelle FROM role";
$result_role = $conn->query($sql_role);

$roles= [];
if ($result_role) {
    while ($row_role = $result_role->fetch_assoc()) {
        $roles[] = $row_role;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Gestion du personnel</title>
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
    <h1>Gestion du personnel</h1>

    <!-- Formulaire d'ajout -->
    <form method="POST" action="" class="form-add">
        <div class="ligne">
            <div class="gauche">
                <label for="nom">Nom :</label>
                <input type="text" name="nom" id="nom" required>
            </div>
            <div class="droite">
                <label for="prenom">Prénom :</label>
                <input type="text" name="prenom" id="prenom" required>
            </div>
        </div>  
        <div class="ligne">
            <div class="gauche">  
                <label for="email">Email :</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="droite">
                <label for="mot_de_passe">Mot de passe :</label>
                <input type="text" name="mot_de_passe" id="mot_de_passe" required>
            </div>
        </div>
        <div class="ligne">
            <div class="gauche">
                <label for="id_role">Role :</label>
                <select name="id_role" id="id_role" style="width: 100%;" required>
                    <option value="" disabled selected>Choix</option>
                    <?php
                    foreach ($roles as $role) {
                        echo "<option value='" . $role['id_role'] . "'>" . $role['libelle'] . "</option>";
                    }
                    ?>
                </select>     
            </div>
            <div class="droite">
                <label for="id_metier">Métier :</label>
                <select name="id_metier" id="id_metier" style="width: 100%;" required>
                    <option value="" disabled selected>Choix</option>
                    <?php
                    foreach ($metiers as $metier) {
                        echo "<option value='" . $metier['id_metier'] . "'>" . $metier['libelle'] . "</option>";
                    }
                    ?>
                </select>
            </div>           
        </div>
        <button type="submit" name="add_personnel">Ajouter personnel</button>
        <?php        
        // Affichage des messages d'erreur, le cas échéant
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
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Mot de passe</th>
                    <th>Rôle</th>
                    <th>Métier</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Récupérer les médecins depuis la base de données
                $sql = "SELECT personnel.* FROM personnel
                iNNER JOIN metier ON personnel.id_metier = metier.id_metier";
                $result = $conn->query($sql);

                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<form method='POST'>";
                    echo "<td>" . $row['id_personnel'] . "</td>";
                    echo "<td><input type='text' name='nom' value='" . $row['nom'] . "' required></td>";
                    echo "<td><input type='text' name='prenom' value='" . $row['prenom'] . "' required></td>";
                    echo "<td><input type='email' name='email' value='" . $row['email'] . "' required></td>";
                    echo "<td><input type='text' name='mot_de_passe' value='" . $row['mot_de_passe'] . "' readonly></td>";
                    // echo "<td><input type='text' name='mot_de_passe_clair' value='" . $row['mot_de_passe_clair'] . "' required></td>";
                    echo "<td>";
                    echo "<select name='id_role' required>";
                    foreach ($roles as $role) {
                        $selected = ($role['id_role'] == $row['id_role']) ? "selected" : "";
                        echo "<option value='" . $role['id_role'] . "' $selected>" . htmlspecialchars($role['libelle']) . "</option>";
                    }
                    echo "</select>";
                    echo "</td>";
                    echo "<td>";
                    echo "<select name='id_metier' required>";
                    foreach ($metiers as $metier) {
                        $selected = ($metier['id_metier'] == $row['id_metier']) ? "selected" : "";
                        echo "<option value='" . $metier['id_metier'] . "' $selected>" . htmlspecialchars($metier['libelle']) . "</option>";
                    }
                    echo "</select>";
                    echo "</td>";

                    // Colonne "Modifier" avec l'icône dans un formulaire
                    echo "<td class='action-icons'>
                                <input type='hidden' name='id_personnel' value='" . $row['id_personnel'] . "'>
                                <button class='icon-button' type='submit' name='edit_personnel'>
                                    <img class='icon' src='Image/modifier.png' alt='Modifier'>
                                </button>
                          </td>";
                    // Colonne "Supprimer"
                    echo "<td class='action-icons'>
                                <button class='icon-button' type='submit' name='delete_personnel'>
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
</body>
</html>
