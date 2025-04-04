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
    <link rel="icon" href="Image/logo.png" type="image/x-icon">
    <link rel="shortcut icon" href="Image/logo.png" type="image/x-icon">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="container">
        <h2>Connexion</h2>
        <form action="" method="post"> 
        <div class="input-group">
            <input type="email" name="email" class="input" value="<?= isset($_SESSION['email_input']) ? $_SESSION['email_input'] : '' ?>" required>
            <label class="user-label">Email</label>
        </div>
            <div class="password-container">
            <div class="input-group">
                <input type="password" name="password" id="password" class="input" required>
                <label class="user-label">Mot de passe</label>
                <span class="toggle-password" onclick="togglePassword()">
                <i id="eye-icon" class="fa fa-eye"></i>
                </span>
            </div>
            </div>
            <p id="message_mdp">Le mot de passe doit contenir au moins 12 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.</p>
            <div class="g-recaptcha" data-sitekey="6Ldf2EYqAAAAAA8UEjk3gMw0fmgFtjVA_qPaxWKm"></div>
            <button type="submit">Se connecter</button>
        </form>
        <!--<div class="create-account">
            <a href="inscription.php">Créez un compte</a>
        </div>-->
        <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Vérifier si le reCAPTCHA a été coché
                if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {

                    // Clé secrète du reCAPTCHA
                    $secretKey = '6Ldf2EYqAAAAADPmXj0tDcBJo97A4GR8f4GzSx9J';  // Mets ici ta clé secrète reCAPTCHA

                    // Récupérer la réponse de l'utilisateur
                    $captchaResponse = $_POST['g-recaptcha-response'];

                    // Vérifier la réponse auprès des serveurs de Google
                    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captchaResponse");
                    $responseKeys = json_decode($response, true);
                    
                    // Si le reCAPTCHA est validé
                    if ($responseKeys["success"]) {
                        // Vérifiez que les champs email et password existent
                        if (isset($_POST['email']) && !empty($_POST['email']) && isset($_POST['password']) && !empty($_POST['password'])) {
                            // Récupérer les données du formulaire
                            $email = $_POST['email'];
                            $password = $_POST['password'];
                            

                            // Préparation de la requête SQL pour vérifier les identifiants et récupérer id_metier
                            $sql = "SELECT p.id_personnel, p.mot_de_passe, p.id_role, m.id_service 
                            FROM personnel p
                            JOIN metier m ON p.id_metier = m.id_metier
                            WHERE p.email = ?";        
                            if ($stmt = $conn->prepare($sql)) {
                                $stmt->bind_param("s", $email);
                                $stmt->execute();
                                $stmt->store_result();
                                $stmt->bind_result($id_utilisateur, $password_bdd, $id_role, $id_service);
                
                                if ($stmt->num_rows > 0) {
                                    // Récupérer le mot de passe haché et id_metier depuis la base de données
                                    $stmt->fetch();
                                    if (password_verify($password, $password_bdd)) {         
                                        $_SESSION['email'] = $email;
                                        $_SESSION['id_utilisateur'] = $id_utilisateur;
                                        $_SESSION['id_role'] = $id_role;
                                        $_SESSION['id_service'] = $id_service;
                                        // Vérification du métier de l'utilisateur
                                        if ($id_role == 1) {  // 1 correspond à 'administrateur'
                                            header("Location: pre_admission.php");
                                            exit();
                                        } if ($id_role == 2) {  // 2 correspond à 'secrétaire'
                                            header("Location: pre_admission.php");
                                            exit();
                                        } if ($id_role == 3) {  // 3 correspond à 'médecin'                                            
                                            header("Location: accueil_medecin.php");
                                            exit();
                                        } else {
                                            // Gestion des autres rôles ou cas par défaut
                                            echo "Rôle non reconnu.";
                                            exit();
                                        }
                                    } else {
                                        // Mot de passe incorrect
                                        $_SESSION['message'] = "<p>Mot de passe incorrect.</p>";
                                        $_SESSION['email_input'] = trim($_POST['email']); // Stocke l'email en session
                                        header("Location: index.php");
                                        exit();
                                    }
                                } else {
                                    // Adresse email non trouvée
                                    $_SESSION['message'] = "<p>Aucun compte trouvé avec cette adresse e-mail.</p>";
                                    $_SESSION['email_input'] = trim($_POST['email']); // Stocke l'email en session
                                    header("Location: index.php");
                                    exit();                                    
                                }

                                if ($stmt) {
                                    $stmt->close();
                                }
                            } else {
                                $_SESSION['message'] = "<p>Erreur lors de la préparation de la requête SQL.</p>";
                                $_SESSION['email_input'] = trim($_POST['email']); // Stocke l'email en session
                                header("Location: index.php");
                                exit();
                            }

                            if ($conn) {
                                $conn->close();
                            }
                        }
                    } else {
                        // Si le reCAPTCHA a échoué
                        $_SESSION['message'] = "<p>Veuillez valider le CAPTCHA pour continuer.</p>";
                        $_SESSION['email_input'] = trim($_POST['email']); // Stocke l'email en session
                        header("Location: index.php");
                        exit();
                    }
                } else {
                    // Si le reCAPTCHA n'est pas coché
                    $_SESSION['message'] = "<p>Veuillez cocher le CAPTCHA.</p>";
                    $_SESSION['email_input'] = trim($_POST['email']); // Stocke l'email en session
                    header("Location: index.php");
                    exit();
                }
            }

            // Affichage des messages d'erreur, le cas échéant
            if (isset($_SESSION['message'])) {
                echo $_SESSION['message'];
                // Supprimer le message de la session après l'avoir affiché
                unset($_SESSION['message']);
            }
        ?>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" /> 
    <script src="script.js"></script>  
</body>
</html>
<script>    
// Fonction pour désactiver le retour en arrière
function preventBack() {
    window.history.forward(); 
}

// Appel la fonction lorsqu'on essaie d'aller en arrière
setTimeout("preventBack()", 0);
</script>