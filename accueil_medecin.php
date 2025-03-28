<?php
session_start();
require 'config.php';
if (!isset($_SESSION['email']) || ($_SESSION['id_role'] != 3 )) {
    // Rediriger vers la page de connexion si la session n'est pas définie
    header("Location: index.php");
    exit();
}

$service = $_SESSION['id_service'];
$libelle_service = "Inconnu";
if ($service > 0) {
    $sql = "SELECT libelle FROM service WHERE id_service = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $service);
    $stmt->execute();
    $stmt->bind_result($libelle);
    
    if ($stmt->fetch()) {
        $libelle_service = htmlspecialchars($libelle); // Sécurisation contre XSS
    }
    $stmt->close();
}
?>

<h1 style="font-family: 'Bebas Neue', sans-serif; margin-top: 1%; font-size: 3vw;">Service <?php echo $libelle; ?></h1>    

<?php
$mois = isset($_GET['mois']) ? (int)$_GET['mois'] : 0;
$annee = isset($_GET['annee']) ? (int)$_GET['annee'] : 0;
$sql = "SELECT h.id_hospitalisation, h.pre_admission, h.date_hospitalisation, h.heure_hospitalisation, 
               pa.nom AS nom_patient, pe.nom AS nom_personnel, c.type_chambre /*,pu.nom AS nom_utilisateur*/
        FROM hospitalisation h
        JOIN patient pa ON h.id_patient = pa.id_patient
        JOIN personnel pe ON h.id_personnel = pe.id_personnel
        JOIN chambre c ON h.id_chambre = c.id_chambre
        JOIN personnel pu ON h.id_utilisateur = pu.id_personnel
        JOIN metier m ON pe.id_metier = m.id_metier
        WHERE m.id_service = ?";


if ($mois > 0) {
    $sql .= " AND MONTH(h.date_hospitalisation) = ?";
} 
if ($annee > 0) {
    $sql .= " AND YEAR(h.date_hospitalisation) = ?";
}
if ($stmt = $conn->prepare($sql)) {
    if ($mois > 0 && $annee > 0) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $service, $mois, $annee);
    } elseif ($mois > 0) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $service, $mois);
    } elseif ($annee > 0) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $service, $annee);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $service);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='table-container'>";
        echo "<table border='1'>";
        echo "<tr><th>Id_hospitalisation</th><th>Pré admission</th><th>Date de l'hospitalisation</th>
                  <th>Heure de l'hospitalisation</th><th>Nom du patient</th><th>Nom du médecin</th>
                  <th>Type de chambre</th></tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id_hospitalisation']}</td>
                    <td>{$row['pre_admission']}</td>
                    <td>{$row['date_hospitalisation']}</td>
                    <td>{$row['heure_hospitalisation']}</td>
                    <td>{$row['nom_patient']}</td>
                    <td>{$row['nom_personnel']}</td>
                    <td>{$row['type_chambre']}</td>
                  </tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "Aucun rendez-vous trouvé pour votre service et ces paramètres.";
    }
    $stmt->close();
}
$conn->close();
?>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Accueil médecin</title>
    <link rel="stylesheet" href="styleadmin.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@1,300&display=swap" rel="stylesheet"/>
    <link rel="icon" href="Image/logo.png" type="image/x-icon">
    <link rel="shortcut icon" href="Image/logo.png" type="image/x-icon">
</head>
<body> 
<a href="logout.php" class="btn_deco">Déconnexion</a>   
<form method="GET" action="" style="position: absolute; top: 10px; left: 10px;">
    <label for="mois" style="font-weight: bolder;">Trier :</label>
    <select name="mois" id="mois" class="filtrage">
        <option value="">Tous</option>
        <?php
        setlocale(LC_TIME, 'fr_FR.UTF-8'); // Définir la langue en français
        $mois_fr = [
            1 => "Janvier", 2 => "Février", 3 => "Mars", 4 => "Avril",
            5 => "Mai", 6 => "Juin", 7 => "Juillet", 8 => "Août",
            9 => "Septembre", 10 => "Octobre", 11 => "Novembre", 12 => "Décembre"
        ];
        
        for ($i = 1; $i <= 12; $i++) {
            $selected = (isset($_GET['mois']) && $_GET['mois'] == $i) ? 'selected' : '';
            echo "<option value='$i' $selected>{$mois_fr[$i]}</option>";
        }
        ?>
    </select>
    <select name="annee" id="annee" class="filtrage">
        <option value="">Toutes</option>
        <?php
        $currentYear = date("Y");
        for ($i = $currentYear - 1; $i <= $currentYear + 2; $i++) {
            $selected = (isset($_GET['annee']) && $_GET['annee'] == $i) ? 'selected' : '';
            echo "<option value='$i' $selected>$i</option>";
        }
        ?>
    </select>
    <button type="submit" class="filtrage">Filtrer</button>
</form>
</body>
</html>
