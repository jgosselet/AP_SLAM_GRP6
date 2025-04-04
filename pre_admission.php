<?php
session_start();
require 'config.php';
if (!isset($_SESSION['email']) || ($_SESSION['id_role'] == 1 && $_SESSION['id_service'] == 2)) {
    header("Location: index.php");
    exit();
}

function verifierNumeroSecu($numero_secu, $civilite, $date_naissance) {
    // Extraire les parties du numéro
    $genre = substr($numero_secu, 0, 1); // Premier chiffre
    $annee_naissance = substr($numero_secu, 1, 2); // 2e et 3e chiffres
    $mois_naissance = substr($numero_secu, 3, 2); // 4e et 5e chiffres

    // Vérifier le genre
    if (($civilite === 'Homme' && $genre != '1') || ($civilite === 'Femme' && $genre != '2')) {
        header("Location: pre-admission.php");
        return $_SESSION['message'] = "Le genre du numéro de sécurité sociale ne correspond pas à la civilité.";
    }

    // Extraire l'année et le mois de naissance du champ date_naissance
    $annee_naissance_reelle = substr($date_naissance, 2, 2); // Année sur deux chiffres
    $mois_naissance_reel = substr($date_naissance, 5, 2); // Mois sur deux chiffres

    // Vérifier l'année de naissance
    if ($annee_naissance != $annee_naissance_reelle) {
        header("Location: pre-admission.php");
        return $_SESSION['message'] = "L'année de naissance dans le numéro de sécurité sociale est incorrecte.";
    }

    // Vérifier le mois de naissance
    if ($mois_naissance != $mois_naissance_reel) {
        header("Location: pre-admission.php");
        return $_SESSION['message'] = "Le mois de naissance dans le numéro de sécurité sociale est incorrect.";
    }

    return true; // Si tout est correct
}


function renderProgressBar($currentStep) {
    // Définir les étapes
    $steps = [
        1 => 'HOSPITALISATION', // Etape 1 : hospitalisation
        2 => 'PATIENT',         // Etape 2 : patient
        3 => 'COUVERTURE SOCIALE',  // Etape 5 : couverture sociale
        4 => 'DOCUMENTS',          // Etape 6 : documents
    ];

    echo '<div class="progress-bar">';
    
    // Parcours de toutes les étapes
    foreach ($steps as $stepNumber => $label) {
        // Initialiser les classes
        $isActive = '';
        $isLineActive = '';

        // Pour HOSPITALISATION et PATIENT : actives jusqu'à l'étape 4
        if ($stepNumber == 1 && $currentStep >= 1) {
            $isActive = 'active';
        } elseif ($stepNumber == 2 && $currentStep >= 2) {
            $isActive = 'active';
        } elseif ($stepNumber == 3 && $currentStep >= 5) {  // COUVERTURE SOCIALE : à partir de l'étape 5
            $isActive = 'active';
        } elseif ($stepNumber == 4 && $currentStep >= 6) {  // DOCUMENTS : à partir de l'étape 6
            $isActive = 'active';
        }

        // Si l'étape courante est déjà passée (ex : l'étape 1 et 2 sont déjà complètes quand on est à l'étape 3)
        if ($currentStep > $stepNumber) {
            $isLineActive = 'active';
        }

        $marginTop = ($stepNumber == 3) ? 'style="margin-top: 3%;"' : '';

        // Affichage du cercle et du label pour chaque étape
        echo '<div class="step ' . $isActive . '" ' . $marginTop . '>';
        echo '<div class="circle">' . $stepNumber . '</div>';
        echo '<div class="label">' . $label . '</div>';
        echo '</div>';

        // Afficher la ligne après chaque cercle (sauf pour la dernière étape)
        if ($stepNumber < 4) { // On évite d'afficher une ligne après la dernière étape
            echo '<div class="line ' . $isLineActive . '"></div>';
        }        
    }

    echo '</div>';
}



// Fonction pour vérifier si les données existent déjà dans la table `proche`
function checkIfPersonExists($conn, $nom, $prenom, $tel, $adresse) {
    // Initialiser la variable $count pour éviter l'erreur
    $count = 0;
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM proche WHERE nom = ? AND prenom = ? AND tel = ? AND adresse = ?");
    $stmt_check->bind_param("ssss", $nom, $prenom, $tel, $adresse);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    return $count > 0; // Retourne true si la personne existe déjà, sinon false
}


// Vérifier et définir l'étape
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1; // Initialiser l'étape si elle n'existe pas
}


// Traitement de la soumission du formulaire de couverture sociale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_hospitalisation'])) {
    // Récupération des données de couverture sociale
    $pre_admission = $_POST['pre_admission'];
    $date_hospitalisation = $_POST['date_hospitalisation'];
    $heure_hospitalisation = $_POST['heure_hospitalisation'];
    $nom_medecin = $_POST['nom_medecin'] ;
    $chambre = $_POST['chambre'];
    
    // Stocker les données de couverture sociale dans la session
    $_SESSION['hospitalisation_data'] = [
        'pre_admission' => $pre_admission,
        'date_hospitalisation' => $date_hospitalisation,
        'heure_hospitalisation' => $heure_hospitalisation,
        'nom_medecin' => $nom_medecin,
        'chambre' => $chambre,
    ];
    

    $_SESSION['step'] = 2; // Changer l'étape à 2
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Traitement de la soumission du formulaire de patient
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_patient'])) {
    // Récupération des données du patient
    $nom = $_POST['nom'];
    $nom_epouse = $_POST['nom_epouse'];
    $prenom = $_POST['prenom'];
    $civilite = $_POST['civilite'];
    $date_naissance = $_POST['date_naissance'];
    $adresse = $_POST['adresse'];
    $code_postal = $_POST['code_postal'];
    $ville = $_POST['ville'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    
    $_SESSION['numero_secu_verif'] = $_POST['numero_secu_verif'];
    
    // Stocker les données du patient dans la session
    $_SESSION['patient_data'] = [
        'nom' => $nom,
        'nom_epouse' => $nom_epouse,
        'prenom' => $prenom,
        'civilite' => $civilite,
        'date_naissance' => $date_naissance,
        'adresse' => $adresse,
        'code_postal' => $code_postal,
        'ville' => $ville,
        'email' => $email,
        'telephone' => $telephone,
    ];
    
    $_SESSION['form_data'] = array_merge($_SESSION['form_data'] ?? [], $_POST);
    // Passer à l'étape suivante
    $_SESSION['step'] = 3; // Changer l'étape à 3
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// Traitement de la soumission du formulaire de la personne à prévenir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_personne_p'])) {
    // Récupération des données de la personne à prévenir 
    $nom_personne_p = $_POST['nom_personne_p'];
    $prenom_personne_p = $_POST['prenom_personne_p'];
    $tel_personne_p = $_POST['tel_personne_p'];
    $adresse_personne_p = $_POST['adresse_personne_p'];

    // Stocker les données de la personne à prévenir dans la session
    $_SESSION['personne_p_data'] = [
        'nom_personne_p' => $nom_personne_p,
        'prenom_personne_p' => $prenom_personne_p,
        'tel_personne_p' => $tel_personne_p,
        'adresse_personne_p' => $adresse_personne_p,
    ];
    
    // Passer à l'étape finale pour valider les données
    $_SESSION['step'] = 4; // Changer l'étape à 4
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Traitement de la soumission du formulaire de la personne de confiance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_personne_c'])) {
    // Récupération des données de la personne à prévenir 
    $nom_personne_c = $_POST['nom_personne_c'];
    $prenom_personne_c = $_POST['prenom_personne_c'];
    $tel_personne_c = $_POST['tel_personne_c'];
    $adresse_personne_c = $_POST['adresse_personne_c'];

    // Stocker les données de la personne à prévenir dans la session
    $_SESSION['personne_c_data'] = [
        'nom_personne_c' => $nom_personne_c,
        'prenom_personne_c' => $prenom_personne_c,
        'tel_personne_c' => $tel_personne_c,
        'adresse_personne_c' => $adresse_personne_c,
    ];
    
    // Passer à l'étape finale pour valider les données
    $_SESSION['step'] = 5; // Changer l'étape à 5
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Traitement de la soumission du formulaire de couverture sociale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_couverture'])) {
    // Récupération des données de couverture sociale
    $numero_secu = $_POST['numero_secu'];
    $organisme = $_POST['organisme'];
    $patient_assurance = $_POST['patient_assurance'];
    $patient_ald = $_POST['patient_ald'];
    $nom_mutuelle = $_POST['nom_mutuelle'];
    $num_adherent = $_POST['num_adherent'];
 
    // Valider le numéro de sécurité sociale
    $civilite = $_SESSION['patient_data']['civilite']; // Récupérer la civilité depuis la session
    $date_naissance = $_SESSION['patient_data']['date_naissance']; // Récupérer la date de naissance depuis la session
    $verif_result = verifierNumeroSecu($numero_secu, $civilite, $date_naissance);
    
    if ($verif_result !== true) {
        $_SESSION['message'] = "<p>$verif_result</p>";
        $_SESSION['couverture_sociale_data']['numero_secu'] = $_POST['numero_secu'];
        $_SESSION['couverture_sociale_data']['organisme'] = $_POST['organisme'];
        $_SESSION['couverture_sociale_data']['patient_assurance'] = $_POST['patient_assurance'];
        $_SESSION['couverture_sociale_data']['patient_ald'] = $_POST['patient_ald'];
        $_SESSION['couverture_sociale_data']['nom_mutuelle'] = $_POST['nom_mutuelle'];
        $_SESSION['couverture_sociale_data']['num_adherent'] = $_POST['num_adherent'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Stocker les données de couverture sociale dans la session
    $_SESSION['couverture_sociale_data'] = [
        'numero_secu' => $numero_secu,
        'organisme' => $organisme,
        'patient_assurance' => $patient_assurance,
        'patient_ald' => $patient_ald,
        'nom_mutuelle' => $nom_mutuelle,
        'num_adherent' => $num_adherent,
    ];
    
    // Ajouter numero_secu également dans les données du patient pour l'insertion
    $_SESSION['patient_data']['numero_secu'] = $numero_secu;

   
    $_SESSION['step'] = 6; // Changer l'étape à 6
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}



// Traitement de la soumission du formulaire de la personne de confiance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_document'])) {
    // Récupération des fichiers obligatoires
    $carte_identite = isset($_FILES['carte_identite']['tmp_name']) && is_uploaded_file($_FILES['carte_identite']['tmp_name'])
    ? file_get_contents($_FILES['carte_identite']['tmp_name'])
    : null;

    $carte_vitale = isset($_FILES['carte_vitale']['tmp_name']) && is_uploaded_file($_FILES['carte_vitale']['tmp_name'])
    ? file_get_contents($_FILES['carte_vitale']['tmp_name'])
    : null;

    $carte_mutuelle = isset($_FILES['carte_mutuelle']['tmp_name']) && is_uploaded_file($_FILES['carte_mutuelle']['tmp_name'])
    ? file_get_contents($_FILES['carte_mutuelle']['tmp_name'])
    : null;

    // Récupération des fichiers facultatifs
    $livret_famille = isset($_FILES['livret_famille']['tmp_name']) && is_uploaded_file($_FILES['livret_famille']['tmp_name'])
    ? file_get_contents($_FILES['livret_famille']['tmp_name'])
    : null;

    $autorisation_soin = isset($_FILES['autorisation_soin']['tmp_name']) && is_uploaded_file($_FILES['autorisation_soin']['tmp_name'])
    ? file_get_contents($_FILES['autorisation_soin']['tmp_name'])
    : null;

    $decision_juge = isset($_FILES['decision_juge']['tmp_name']) && is_uploaded_file($_FILES['decision_juge']['tmp_name'])
    ? file_get_contents($_FILES['decision_juge']['tmp_name'])
    : null;
   
    // Stocker les données des fichiers dans la session
    $_SESSION['document_data'] = [
        'carte_identite' => $carte_identite,
        'carte_vitale' => $carte_vitale,
        'carte_mutuelle' => $carte_mutuelle,
        'livret_famille' => $livret_famille,
        'autorisation_soin' => $autorisation_soin,
        'decision_juge' => $decision_juge,
    ];
    
    // Passer à l'étape finale pour valider les données
    $_SESSION['step'] = 7; // Changer l'étape à 7
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


if (isset($_POST['submit_prec']) && $_SESSION['step'] > 1) {
    $_SESSION['form_data'] = array_merge($_SESSION['form_data'] ?? [], $_POST);
    $_SESSION['step'] -= 1; // Retourner à l'étape précédente
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;

}

// Traitement de la soumission finale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_final']) && $_SESSION['step'] === 7) {
    $patient_data = $_SESSION['patient_data'];
    $couverture_data = $_SESSION['couverture_sociale_data'];
    $hospitalisation_data = $_SESSION['hospitalisation_data'];
    $personne_p_data = $_SESSION['personne_p_data'];
    $personne_c_data = $_SESSION['personne_c_data'];
    $document_data = $_SESSION['document_data'];

    // Démarrer une transaction
    $conn->begin_transaction();

    try {
        // Étape 1 : Vérifier si la personne à prévenir existe déjà
        if (!checkIfPersonExists($conn, $personne_p_data['nom_personne_p'], $personne_p_data['prenom_personne_p'], $personne_p_data['tel_personne_p'], $personne_p_data['adresse_personne_p'])) {
            // Si la personne à prévenir n'existe pas, on l'insère
            $stmt_proche = $conn->prepare("INSERT INTO proche (nom, prenom, tel, adresse) VALUES (?, ?, ?, ?)");
            $stmt_proche->bind_param(
                "ssss",
                $personne_p_data['nom_personne_p'],
                $personne_p_data['prenom_personne_p'],
                $personne_p_data['tel_personne_p'],
                $personne_p_data['adresse_personne_p']
            );

            if (!$stmt_proche->execute()) {
                throw new Exception("Erreur lors de l'enregistrement de la personne à prévenir: " . $stmt_proche->error);
            }

            // Récupérer l'ID généré pour la personne à prévenir
            $id_personne_prevenir = $conn->insert_id;
        } else {
            // Si la personne à prévenir existe déjà, récupérer son ID
            $stmt_check = $conn->prepare("SELECT id_personne FROM proche WHERE nom = ? AND prenom = ? AND tel = ? AND adresse = ?");
            $stmt_check->bind_param("ssss", $personne_p_data['nom_personne_p'], $personne_p_data['prenom_personne_p'], $personne_p_data['tel_personne_p'], $personne_p_data['adresse_personne_p']);
            $stmt_check->execute();
            $stmt_check->bind_result($id_personne_prevenir);
            $stmt_check->fetch();
            // Libère la mémoire associée à la requête
            $stmt_check->free_result();
        }

        // Étape 2 : Vérifier si la personne de confiance existe déjà
        if (!checkIfPersonExists($conn, $personne_c_data['nom_personne_c'], $personne_c_data['prenom_personne_c'], $personne_c_data['tel_personne_c'], $personne_c_data['adresse_personne_c'])) {
            // Si la personne de confiance n'existe pas, on l'insère
            $stmt_confiance = $conn->prepare("INSERT INTO proche (nom, prenom, tel, adresse) VALUES (?, ?, ?, ?)");
            $stmt_confiance->bind_param(
                "ssss",
                $personne_c_data['nom_personne_c'],
                $personne_c_data['prenom_personne_c'],
                $personne_c_data['tel_personne_c'],
                $personne_c_data['adresse_personne_c']
            );

            if (!$stmt_confiance->execute()) {
                throw new Exception("Erreur lors de l'enregistrement de la personne de confiance: " . $stmt_confiance->error);
            }

            // Récupérer l'ID généré pour la personne de confiance
            $id_personne_confiance = $conn->insert_id;
        } else {
            // Si la personne de confiance existe déjà, récupérer son ID
            $stmt_check = $conn->prepare("SELECT id_personne FROM proche WHERE nom = ? AND prenom = ? AND tel = ? AND adresse = ?");
            $stmt_check->bind_param("ssss", $personne_c_data['nom_personne_c'], $personne_c_data['prenom_personne_c'], $personne_c_data['tel_personne_c'], $personne_c_data['adresse_personne_c']);
            $stmt_check->execute();
            $stmt_check->bind_result($id_personne_confiance);
            $stmt_check->fetch();
            // Libère la mémoire associée à la requête
            $stmt_check->free_result();
        }


        // Étape 3 : Insérer les documents
        $stmt_document = $conn->prepare ("INSERT INTO document (carte_identite, carte_vitale, carte_mutuelle, livret_famille, autorisation_soin, decision_juge) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_document->bind_param("ssssss",
            $document_data['carte_identite'],
            $document_data['carte_vitale'],
            $document_data['carte_mutuelle'],
            $document_data['livret_famille'],
            $document_data['autorisation_soin'],
            $document_data['decision_juge']
        );

        if (!$stmt_document->execute()) {
            throw new Exception("Erreur lors de l'enregistrement des documents " . $stmt_document->error);
        }

        // Récupérer l'ID généré pour les documents
        $id_document = $conn->insert_id;

        // Étape 4 : Vérifier si le numéro de sécurité sociale existe déjà
        $stmt_check = $conn->prepare("SELECT id_patient FROM patient WHERE numero_secu = ?");
        $stmt_check->bind_param("s", $patient_data['numero_secu']);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Le numéro de sécurité sociale existe déjà, récupération de l'ID du patient
            $stmt_check->bind_result($id_patient);
            $stmt_check->fetch();

            // Mettre à jour la ligne correspondante
            $stmt_update = $conn->prepare("UPDATE patient 
                                        SET nom = ?, nom_epouse = ?, prenom = ?, civilite = ?, date_naissance = ?, 
                                            adresse = ?, cp = ?, ville = ?, email = ?, tel = ?, 
                                            id_personne_prevenir = ?, id_personne_confiance = ?, id_document = ?
                                        WHERE id_patient = ?");
            $stmt_update->bind_param(
                "ssssssssssiiii",
                $patient_data['nom'],
                $patient_data['nom_epouse'],
                $patient_data['prenom'],
                $patient_data['civilite'],
                $patient_data['date_naissance'],
                $patient_data['adresse'],
                $patient_data['code_postal'],
                $patient_data['ville'],
                $patient_data['email'],
                $patient_data['telephone'],
                $id_personne_prevenir,
                $id_personne_confiance,
                $id_document,
                $id_patient
            );

            if (!$stmt_update->execute()) {
                throw new Exception("Erreur lors de la mise à jour du patient : " . $stmt_update->error);
            }
        } else {
            // Le numéro de sécurité sociale n'existe pas, insérer un nouveau patient
            $stmt_patient = $conn->prepare("INSERT INTO patient (nom, nom_epouse, prenom, civilite, date_naissance, adresse, cp, ville, email, tel, numero_secu, id_personne_prevenir, id_personne_confiance, id_document) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_patient->bind_param(
                "sssssssssssiii",
                $patient_data['nom'],
                $patient_data['nom_epouse'],
                $patient_data['prenom'],
                $patient_data['civilite'],
                $patient_data['date_naissance'],
                $patient_data['adresse'],
                $patient_data['code_postal'],
                $patient_data['ville'],
                $patient_data['email'],
                $patient_data['telephone'],
                $patient_data['numero_secu'],
                $id_personne_prevenir,
                $id_personne_confiance,
                $id_document
            );

            if (!$stmt_patient->execute()) {
                throw new Exception("Erreur lors de l'enregistrement du patient : " . $stmt_patient->error);
            }

            // Récupérer l'ID du patient inséré
            $id_patient = $conn->insert_id;
        }

        // Libération des ressources
        $stmt_check->close();

        
        // Étape 4 : Insérer ou mettre à jour la couverture sociale dans la table `couverture_sociale`
        $stmt_couverture = $conn->prepare("INSERT INTO couverture_sociale (numero_secu, organisme, patient_assurance, patient_ald, nom_mutuelle, num_adherent) VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            organisme = VALUES(organisme),
            patient_assurance = VALUES(patient_assurance),
            patient_ald = VALUES(patient_ald),
            nom_mutuelle = VALUES(nom_mutuelle),
            num_adherent = VALUES(num_adherent)
        ");

        $stmt_couverture->bind_param(
        "issssi",
        $couverture_data['numero_secu'],      
        $couverture_data['organisme'],       
        $couverture_data['patient_assurance'],
        $couverture_data['patient_ald'],      
        $couverture_data['nom_mutuelle'],     
        $couverture_data['num_adherent']      
        );

        if (!$stmt_couverture->execute()) {
        throw new Exception("Erreur lors de l'enregistrement de la couverture sociale : " . $stmt_couverture->error);
        }

        

       // Étape 5 : Insérer l'hospitalisation dans la table `hospitalisation`
        $stmt_hospitalisation = $conn->prepare("INSERT INTO hospitalisation (pre_admission, date_hospitalisation, heure_hospitalisation, id_patient, id_personnel, id_chambre, id_utilisateur)  VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_hospitalisation->bind_param(
        "sssiiii", // Mise à jour du type de paramètres
        $hospitalisation_data['pre_admission'],
        $hospitalisation_data['date_hospitalisation'],
        $hospitalisation_data['heure_hospitalisation'],
        $id_patient, // Utilisez l'ID patient récupéré
        $hospitalisation_data['nom_medecin'],
        $hospitalisation_data['chambre'],
        $_SESSION['id_utilisateur'] // ID de l'utilisateur depuis la session
        );

        if (!$stmt_hospitalisation->execute()) {
            throw new Exception("Erreur lors de l'enregistrement de l'hospitalisation: " . $stmt_hospitalisation->error);
        }


        // Étape 6 : Valider la transaction
        $conn->commit();

        $_SESSION['message'] = "<p>Toutes les informations ont été enregistrées avec succès.</p>";

        // Réinitialiser les données de session
        unset($_SESSION['patient_data']);
        unset($_SESSION['couverture_sociale_data']);
        unset($_SESSION['hospitalisation_data']);
        unset($_SESSION['personne_p_data']);
        unset($_SESSION['personne_c_data']);
        unset($_SESSION['document_data']);
        $_SESSION['step'] = 1; // Revenir à l'étape initiale
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;

    } catch (Exception $e) {
        // En cas d'échec, annuler la transaction
        $conn->rollback();
        $_SESSION['message'] = "<p>Erreur lors de l'enregistrement : </p>" . $e->getMessage();
    }

    // Fermer les déclarations
    if (isset($stmt_proche)) $stmt_proche->close();
    if (isset($stmt_confiance)) $stmt_confiance->close();
    if (isset($stmt_document)) $stmt_document->close();
    if (isset($stmt_patient)) $stmt_patient->close();
    if (isset($stmt_couverture)) $stmt_couverture->close();
    if (isset($stmt_hospitalisation)) $stmt_hospitalisation->close();
}


// Requête pour récupérer les médecins 
$sql = "SELECT p.id_personnel, p.nom, m.libelle FROM personnel p
    JOIN metier m ON p.id_metier = m.id_metier
    WHERE p.id_role = 3";
$result = $conn->query($sql);

if (!$result) {
    die("Erreur lors de l'exécution de la requête : " . $conn->error);
}

// Stocker les résultats dans un tableau
$medecins = [];
while ($row = $result->fetch_assoc()) {
    $medecins[] = $row;
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
    <title>Pré-admission</title>
    <link rel="stylesheet" href="stylepre_admission.css" />
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
        <div class="formulaire">
            <?php if ($_SESSION['step'] == 1): // Afficher le formulaire d'hospitalisation' ?>
                <h1>Hospitalisation</h1>
                <?php renderProgressBar($_SESSION['step']); ?>
                <form method="post" action="">
                    <label for="pre_admission">Pré admission pour :</label> 
                    <select name="pre_admission" id="pre_admission" required>
                        <option value="" disabled selected <?php echo empty($_SESSION['hospitalisation_data']['pre_admission']) ? 'selected' : ''; ?>>Choix</option>
                        <option value="Ambulatoire chirurgie" <?php echo ($_SESSION['hospitalisation_data']['pre_admission'] ?? '') === 'Ambulatoire chirurgie' ? 'selected' : ''; ?>>Ambulatoire chirurgie</option>
                        <option value="Hospitalisation" <?php echo ($_SESSION['hospitalisation_data']['pre_admission'] ?? '') === 'Hospitalisation' ? 'selected' : ''; ?>>Hospitalisation (au moins une nuit)</option>
                    </select>
                    <div class="ligne">
                        <div class="gauche">
                            <label for="date_hospitalisation">Date d'hospitalisation :</label>
                            <input type="date" name="date_hospitalisation" id="date_hospitalisation" min="<?= date('Y-m-d'); ?>" max="2027-01-01" value="<?php echo htmlspecialchars($_SESSION['hospitalisation_data']['date_hospitalisation'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                        <div class="droite">
                            <label for="heure_hospitalisation">Heure d'hospitalisation :</label> 
                            <input type="time" name="heure_hospitalisation" id="heure_hospitalisation"  value="<?php echo htmlspecialchars($_SESSION['hospitalisation_data']['heure_hospitalisation'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                    </div>
                    <label for="nom_medecin">Nom du médecin :</label>
                    <select name="nom_medecin" id="nom_medecin" required>
                        <option value="" disabled selected <?php echo empty($_SESSION['hospitalisation_data']['nom_medecin']) ? 'selected' : ''; ?>>Choix</option>
                        <?php foreach ($medecins as $medecin): ?>
                            <option value="<?php echo htmlspecialchars($medecin['id_personnel'], ENT_QUOTES); ?>" 
                                <?php echo ($_SESSION['hospitalisation_data']['nom_medecin'] ?? '') == $medecin['id_personnel'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($medecin['nom'] . ' - ' . $medecin['libelle'], ENT_QUOTES); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="chambre">Chambre :</label> 
                    <select name="chambre" id="chambre" required>
                        <option value="" disabled selected <?php echo empty($_SESSION['hospitalisation_data']['chambre']) ? 'selected' : ''; ?>>Choix</option>
                        <option value="1" <?php echo ($_SESSION['hospitalisation_data']['chambre'] ?? '') === '1' ? 'selected' : ''; ?>>Simple</option>
                        <option value="2" <?php echo ($_SESSION['hospitalisation_data']['chambre'] ?? '') === '2' ? 'selected' : ''; ?>>Double</option>
                    </select>
                    <button type="submit" name="submit_hospitalisation">Suivant</button>
                </form>
            <?php elseif ($_SESSION['step'] == 2): // Afficher le formulaire de patient ?>
                <div id="session-step" data-step="<?php echo $_SESSION['step']; ?>"></div>
                <h1>Patient</h1>
                <?php renderProgressBar($_SESSION['step']); ?>
                <form method="post" action="" >
                <label for="numero_secu_verif">Numéro de sécurité sociale pour vérifier et remplir automatiquement:</label>
                    <input type="text" name="numero_secu_verif" id="numero_secu_verif" minlength="15" maxlength="15">
                    <button type="button" id="verifier-patient" class="verif-button" >Vérifier</button>
                    <div id="result-message" style="margin-top:10px; color:red;"></div>
                    <div class="ligne">               
                        <div class="gauche">
                            <label for="nom">Nom :</label>
                            <input type="text" name="nom" id="nom" pattern="[A-Za-z]+" value="<?php echo htmlspecialchars($_SESSION['patient_data']['nom'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                        <div class="droite">
                            <label for="nom_epouse">Nom d'épouse :</label>
                            <input type="text" name="nom_epouse" id="nom_epouse" pattern="[A-Za-z]+" value="<?php echo htmlspecialchars($_SESSION['patient_data']['nom_epouse'] ?? '', ENT_QUOTES); ?>">
                        </div>
                    </div>
                    <label for="prenom">Prénom :</label>
                    <input type="text" name="prenom" id="prenom" pattern="[A-Za-z]+" value="<?php echo htmlspecialchars($_SESSION['patient_data']['prenom'] ?? '', ENT_QUOTES); ?>" required>
                    <div class="ligne">
                        <div class="gauche">
                            <label for="civilité">Civilité :</label>
                            <select name="civilite" id="civilite" required>
                                <option value="" disabled <?php echo empty($_SESSION['patient_data']['civilite']) ? 'selected' : ''; ?>>Choix</option>
                                <option value="Homme" <?php echo ($_SESSION['patient_data']['civilite'] ?? '') === 'Homme' ? 'selected' : ''; ?>>Homme</option>
                                <option value="Femme" <?php echo ($_SESSION['patient_data']['civilite'] ?? '') === 'Femme' ? 'selected' : ''; ?>>Femme</option>
                            </select>
                        </div>
                        <div class="droite">
                            <label for="date_naissance">Date de naissance :</label>
                            <input type="date" name="date_naissance" id="date_naissance" min="1904-01-01" max="<?= date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($_SESSION['patient_data']['date_naissance'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                    </div>
                    <label for="adresse">Adresse :</label>
                    <input type="text" name="adresse" id="adresse" value="<?php echo htmlspecialchars($_SESSION['patient_data']['adresse'] ?? '', ENT_QUOTES); ?>" required>
                    <div class="ligne">
                        <div class="gauche">
                            <label for="code_postal">Code postal :</label>
                            <input type="text" name="code_postal" id="code_postal" minlength="5" maxlength="5" pattern="[0-9]*" value="<?php echo htmlspecialchars($_SESSION['patient_data']['code_postal'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                        <div class="droite">
                            <label for="ville">Ville :</label>
                            <input list="villes" name="ville" id="ville" value="<?php echo htmlspecialchars($_SESSION['patient_data']['ville'] ?? '', ENT_QUOTES); ?>" required>                       
                        </div>
                    </div>               
                    <div class="ligne">
                        <div class="gauche">
                            <label for="email">Email :</label>
                            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_SESSION['patient_data']['email'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                        <div class="droite">
                            <label for="tel">Téléphone :</label>
                            <input type="tel" name="telephone" id="telephone" minlength="10" maxlength="10" pattern="[0-9]*" value="<?php echo htmlspecialchars($_SESSION['patient_data']['telephone'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                    </div>
                    <div class="ligne">
                        <div class="gauche">
                            <button type="button" name="submit_prec" onclick="goToPreviousStep()" formnovalidate>Précédent</button>
                        </div>
                        <div class="droite">
                            <button type="submit" name="submit_patient" class="verif-button">Suivant</button>
                        </div>
                    </div>
                </form>
            <?php elseif ($_SESSION['step'] == 3): // Afficher le formulaire de personne à prévenir'?>
                <div id="session-step" data-step="<?php echo $_SESSION['step']; ?>"></div>
                <h1>Personne à prévenir</h1>
                <?php renderProgressBar($_SESSION['step']); ?>
                <form method="post" action="">
                    <input type="text" name="numero_secu_verif" id="numero_secu_verif" minlength="15" maxlength="15" value="<?php echo htmlspecialchars($_SESSION['numero_secu_verif'] ?? '', ENT_QUOTES); ?>" style="display: none;"/>
                    <button type="button" id="verifier-patient" class="verif-button" >Remplir</button>
                    <div class="ligne">
                        <div class="gauche">
                            <label for="nom_personne_p">Nom :</label>  
                            <input type="text" name="nom_personne_p" id="nom_personne_p" value="<?php echo htmlspecialchars($_SESSION['personne_p_data']['nom_personne_p'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                        <div class="droite">
                            <label for="prenom_personne_p">Prénom :</label>
                            <input type="text" name="prenom_personne_p" id="prenom_personne_p" value="<?php echo htmlspecialchars($_SESSION['personne_p_data']['prenom_personne_p'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                    </div>
                    <label for="tel_personne_p">Téléphone :</label> 
                    <input type="tel" name="tel_personne_p" id="tel_personne_p" minlength="10" maxlength="10" pattern="[0-9]*" value="<?php echo htmlspecialchars($_SESSION['personne_p_data']['tel_personne_p'] ?? '', ENT_QUOTES); ?>" required>
                    <label for="adresse_personne_p">Adresse :</label> 
                    <input type="text" name="adresse_personne_p" id="adresse_personne_p" value="<?php echo htmlspecialchars($_SESSION['personne_p_data']['adresse_personne_p'] ?? '', ENT_QUOTES); ?>" required>
                    <div class="ligne">
                        <div class="gauche">
                            <button type="button" name="submit_prec" onclick="goToPreviousStep()" formnovalidate>Précédent</button>
                        </div>
                        <div class="droite">
                            <button type="submit" name="submit_personne_p" class="verif-button">Suivant</button>
                        </div>
                    </div>
                </form>            
            <?php elseif ($_SESSION['step'] == 4): // Afficher le formulaire de personne de confiance'?>
                <div id="session-step" data-step="<?php echo $_SESSION['step']; ?>"></div>
                <div id="personne_p_data" 
                    data-nom-personne-p="<?php echo htmlspecialchars($_SESSION['personne_p_data']['nom_personne_p'] ?? ''); ?>"
                    data-prenom-personne-p="<?php echo htmlspecialchars($_SESSION['personne_p_data']['prenom_personne_p'] ?? ''); ?>"
                    data-tel-personne-p="<?php echo htmlspecialchars($_SESSION['personne_p_data']['tel_personne_p'] ?? ''); ?>"
                    data-adresse-personne-p="<?php echo htmlspecialchars($_SESSION['personne_p_data']['adresse_personne_p'] ?? ''); ?>">
                </div>
                <h1>Personne de confiance</h1>
                <?php renderProgressBar($_SESSION['step']); ?>
                <form method="post" action="" >
                    <input type="text" name="numero_secu_verif" id="numero_secu_verif" minlength="15" maxlength="15" value="<?php echo htmlspecialchars($_SESSION['numero_secu_verif'] ?? '', ENT_QUOTES); ?>" style="display: none;"/>
                    <button type="button" id="verifier-patient" class="verif-button" >Remplir</button>  
                    <!-- Checkbox pour remplir les données automatiquement -->
                    <label>
                        <input type="checkbox" id="sameAsPrev" onclick="copyPersonneData()" style="width: 10%;"> La personne de confiance est la même que la personne à prévenir
                    </label>  
                    <div class="ligne">
                        <div class="gauche">
                            <label for="nom_personne_c">Nom :</label> 
                            <input type="text" name="nom_personne_c" id="nom_personne_c" value="<?php echo htmlspecialchars($_SESSION['personne_c_data']['nom_personne_c'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                        <div class="droite">
                            <label for="prenom_personne_c">Prénom :</label>
                            <input type="text" name="prenom_personne_c" id="prenom_personne_c" value="<?php echo htmlspecialchars($_SESSION['personne_c_data']['prenom_personne_c'] ?? '', ENT_QUOTES); ?>" required>
                        </div>
                    </div>
                    <label for="tel_personne_c">Téléphone :</label>
                    <input type="tel" name="tel_personne_c" id="tel_personne_c" minlength="10" maxlength="10" pattern="[0-9]*" value="<?php echo htmlspecialchars($_SESSION['personne_c_data']['tel_personne_c'] ?? '', ENT_QUOTES); ?>" required>
                    <label for="adresse_personne_c">Adresse :</label> 
                    <input type="text" name="adresse_personne_c" id="adresse_personne_c" value="<?php echo htmlspecialchars($_SESSION['personne_c_data']['adresse_personne_c'] ?? '', ENT_QUOTES); ?>" required>
                    <div class="ligne">
                        <div class="gauche">
                            <button type="button" name="submit_prec" onclick="goToPreviousStep()" formnovalidate>Précédent</button>
                        </div>
                        <div class="droite">
                            <button type="submit" name="submit_personne_c" class="verif-button">Suivant</button>
                        </div>
                    </div>
                </form>
            <?php elseif ($_SESSION['step'] == 5): // Afficher le formulaire de couverture sociale ?>
                <div id="session-step" data-step="<?php echo $_SESSION['step']; ?>"></div>
                <h1>Couverture sociale</h1>
                <?php renderProgressBar($_SESSION['step']); ?>
                <form method="post" action="" >
                    <input type="text" name="numero_secu_verif" id="numero_secu_verif" minlength="15" maxlength="15" value="<?php echo htmlspecialchars($_SESSION['numero_secu_verif'] ?? '', ENT_QUOTES); ?>" style="display: none;"/>
                    <button type="button" id="verifier-patient" class="verif-button" >Remplir</button>
                    <label for="numero_secu">Numéro de sécurité sociale :</label>
                    <input type="text" name="numero_secu" id="numero_secu" minlength="15" maxlength="15" pattern="[0-9]*" value="<?php echo htmlspecialchars($_SESSION['couverture_sociale_data']['numero_secu'] ?? '', ENT_QUOTES); ?>" required>
                    <label for="organisme">Organisme :</label>
                    <input type="text" name="organisme" id="organisme" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+" value="<?php echo htmlspecialchars($_SESSION['couverture_sociale_data']['organisme'] ?? '', ENT_QUOTES); ?>" required>
                    <div class="ligne">
                        <div class="gauche">
                            <label for="patient_assurance">Assurance :</label>
                            <select name="patient_assurance" id="patient_assurance" required>
                                <option value="" disabled selected <?php echo empty($_SESSION['couverture_sociale_data']['patient_assurance']) ? 'selected' : ''; ?>>Choix</option>
                                <option value="Oui" <?php echo ($_SESSION['couverture_sociale_data']['patient_assurance'] ?? '') === 'Oui' ? 'selected' : ''; ?>>Oui</option>
                                <option value="Non" <?php echo ($_SESSION['couverture_sociale_data']['patient_assurance'] ?? '') === 'Non' ? 'selected' : ''; ?>>Non</option>
                            </select>
                        </div>
                        <div class="droite">
                            <label for="patient_ald">ALD :</label>
                            <select name="patient_ald" id="patient_ald" required>
                                <option value="" disabled selected <?php echo empty($_SESSION['couverture_sociale_data']['patient_ald']) ? 'selected' : ''; ?>>Choix</option>
                                <option value="Oui" <?php echo ($_SESSION['couverture_sociale_data']['patient_ald'] ?? '') === 'Oui' ? 'selected' : ''; ?>>Oui</option>
                                <option value="Non" <?php echo ($_SESSION['couverture_sociale_data']['patient_ald'] ?? '') === 'Non' ? 'selected' : ''; ?>>Non</option>
                            </select>
                        </div>
                    </div>
                    <label for="nom_mutuelle">Nom de la mutuelle :</label>
                    <input type="text" name="nom_mutuelle" id="nom_mutuelle" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+" value="<?php echo htmlspecialchars($_SESSION['couverture_sociale_data']['nom_mutuelle'] ?? '', ENT_QUOTES); ?>" required>
                    <label for=num_adherent>Numéro d'adhérent</label>
                    <input type="text" name="num_adherent" id="num_adherent" pattern="[0-9]*" value="<?php echo htmlspecialchars($_SESSION['couverture_sociale_data']['num_adherent'] ?? '', ENT_QUOTES); ?>" required>
                    <div class="ligne">
                        <div class="gauche">
                            <button type="button" name="submit_prec" onclick="goToPreviousStep()" formnovalidate>Précédent</button>
                        </div>
                        <div class="droite">
                            <button type="submit" name="submit_couverture">Suivant</button>
                        </div>
                    </div>
                </form>
            <?php elseif ($_SESSION['step'] == 6): // Afficher le formulaire des documents'?>
                <h1>Document</h1>
                <?php renderProgressBar($_SESSION['step']); ?>
                <form method="post" action="" enctype="multipart/form-data">    
                    <div class="ligne">
                        <div class="gauche">
                            <div class="container-folder">
                                <div class="folder">
                                    <div class="top"></div>
                                    <div class="bottom"></div>
                                </div>
                                <label for="carte_identite" class="custom-file-upload">
                                <input type="file" name="carte_identite" id="carte_identite" accept="application/pdf" onchange="displayFileName('carte_identite')" required>
                                    Carte d'identité
                                </label>
                                <span id="carte_identite_msg" class="file-message"></span>
                            </div>
                        </div>
                        <div class="droite">
                        <div class="container-folder">
                                <div class="folder">
                                    <div class="top"></div>
                                    <div class="bottom"></div>
                                </div>
                                <label for="carte_vitale" class="custom-file-upload">
                                <input type="file" name="carte_vitale" id="carte_vitale" accept="application/pdf" onchange="displayFileName('carte_vitale')" required>
                                    Carte vitale
                                </label>
                                <span id="carte_vitale_msg" class="file-message"></span>
                            </div>
                        </div>   
                    </div>
                    <div class="ligne">
                        <div class="gauche">
                            <div class="container-folder">
                                <div class="folder">
                                    <div class="top"></div>
                                    <div class="bottom"></div>
                                </div>
                                <label for="carte_mutuelle" class="custom-file-upload">
                                <input type="file" name="carte_mutuelle" id="carte_mutuelle" accept="application/pdf" onchange="displayFileName('carte_mutuelle')" required>
                                    Carte mutuelle
                                </label>
                                <span id="carte_mutuelle_msg" class="file-message"></span>
                            </div>
                        </div>
                        <div class="droite">
                        <div class="container-folder">
                                <div class="folder">
                                    <div class="top"></div>
                                    <div class="bottom"></div>
                                </div>
                                <label for="livret_famille" class="custom-file-upload">
                                <input type="file" name="livret_famille" id="livret_famille" accept="application/pdf" onchange="displayFileName('livret_famille')">
                                    Livret de famille
                                </label>
                                <span id="livret_famille_msg" class="file-message"></span>
                            </div>
                        </div>   
                    </div>
                    <div class="ligne">
                        <div class="gauche">
                            <div class="container-folder">
                                <div class="folder">
                                    <div class="top"></div>
                                    <div class="bottom"></div>
                                </div>
                                <label for="autorisation_soin" class="custom-file-upload">
                                <input type="file" name="autorisation_soin" id="autorisation_soin" accept="application/pdf" onchange="displayFileName('autorisation_soin')">
                                    Autorisation de soin
                                </label>
                                <span id="autorisation_soin_msg" class="file-message"></span>
                            </div>
                        </div>
                        <div class="droite">
                        <div class="container-folder">
                                <div class="folder">
                                    <div class="top"></div>
                                    <div class="bottom"></div>
                                </div>
                                <label for="decision_juge" class="custom-file-upload">
                                <input type="file" name="decision_juge" id="decision_juge" accept="application/pdf" onchange="displayFileName('decision_juge')">
                                    Décision du juge
                                </label>
                                <span id="decision_juge_msg" class="file-message"></span>
                            </div>
                        </div>   
                    </div>
                    <div class="ligne">
                        <div class="gauche">
                            <button type="button" name="submit_prec" onclick="goToPreviousStep()" formnovalidate>Précédent</button>
                        </div>
                        <div class="droite">
                        <button type="submit" name="submit_document">Suivant</button>
                        </div>
                    </div>
                </form>
            <?php elseif ($_SESSION['step'] == 7): // Afficher la confirmation finale ?>
                <h1>Générer une fiche de rdv ?</h1>
                <form action="generate_pdf.php" method="POST">
                    <button type="submit" class="btn_deco" id="btn_pdf" style="position: relative; top: 0px; right: 0px; margin: 0; display: flex; justify-content: center; margin: 0 auto;">Générer le PDF</button>
                </form>
                <form method="post" action="">
                <div class="ligne">                
                    <div class="gauche">
                        <button type="button" name="submit_prec" onclick="goToPreviousStep()" formnovalidate>Précédent</button>
                    </div>
                    <div class="droite">                        
                        <button type="submit" name="submit_final">Enregistrer la pré-admission</button>                        
                    </div>               
                </div>
                </form>            
            <?php endif; ?>

            <?php
            // Affichage des messages d'erreur, le cas échéant
            if (isset($_SESSION['message'])) {
                echo $_SESSION['message'];
                unset($_SESSION['message']);
            }
            ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="script.js"></script>      
</body>
</html>
<script>
    var step = <?php echo $_SESSION['step'] ?? 0; ?>; // On récupère la valeur de $_SESSION['step']
</script>