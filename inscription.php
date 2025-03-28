<?php
session_start();
require 'config.php';
?>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Clinique lpfs</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@1,300&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" /> 
</head>
<body>
    <div class="container">
        <h2>Inscription</h2>
        <form action="inscription.php" method="post">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="prenom" placeholder="Prenom" required>
            <input type="email" name="email" placeholder="Adresse e-mail" required>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Mot de passe" required>
                <span class="toggle-password" onclick="togglePassword()">
                <i id="eye-icon" class="fa fa-eye"></i>
                </span>
            </div>
            <input type="number" name="id_role" placeholder="id_role" required>
            <input type="number" name="id_metier" placeholder="id_metier" required> 
            <button type="submit">Créer le compte</button>
        </form>
        <div class="create-account">
            <a href="index.php">Se connecter</a>
        </div>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['email']) && !empty($_POST['email']) && isset($_POST['password']) && !empty($_POST['password'])) {
                $nom = $_POST['nom'];
                $prenom = $_POST['prenom'];
                $email = $_POST['email'];
                $password = $_POST['password'];
                $id_role = $_POST['id_role'];  
                $id_metier = $_POST['id_metier'];
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            if (!validate_password($password)) {
                echo "<p>Le mot de passe doit contenir au moins 12 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.</p>";
            } else {                
                // Préparation de la requête SQL pour insérer les données dans la table utilisateur
                $sql = "INSERT INTO personnel (nom, prenom, email, mot_de_passe, mot_de_passe_clair, id_role, id_metier) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                // Vérifiez si la préparation de la requête a réussi
                if ($stmt === false) {
                    die("Erreur de préparation de la requête: " . $conn->error);
                }

                $stmt->bind_param("sssssii",$nom, $prenom, $email, $hashed_password, $password, $id_role, $id_metier);

                // Exécuter la requête et vérifier si elle a réussi
                if ($stmt->execute()) {
                    $_SESSION['message'] = "<p>Compte créé avec succès !</p>";
                    header("Location: inscription.php"); // Assurez-vous que cette URL correspond à votre page
                    exit();
                } else {
                    $_SESSION['message'] = "<p>Erreur: " . $stmt->error . "</p>";
                }

                // Fermeture de la requête préparée et de la connexion
                $stmt->close();
                $conn->close();
            }
        } else {
            $_SESSION['message'] = "<p>Les champs email ou mot de passe sont manquants.</p>";
        }
    }

    function validate_password($password) {
        // Expression régulière pour valider le mot de passe
        $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{12,}$/';
        return preg_match($pattern, $password);
    }

    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        // Supprimer le message de la session après l'avoir affiché
        unset($_SESSION['message']);
    }
        ?>
    </div>
    <script src="script.js"></script>  
</body>
</html>
