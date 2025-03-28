<?php

use Random\Engine\Secure;

session_start();
require('fpdf/fpdf.php');
require 'config.php';

class PDF extends FPDF {
    function Header() {
        // Ajouter le logo en haut à droite
        $this->Image('Image/logo.png', 150, 10, 50);
        
        // Titre centré
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, mb_convert_encoding('CONFIRMATION DE RENDEZ-VOUS', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(10);
    }
}

$conn->set_charset("utf8");
$nom = $_SESSION['patient_data']['nom'];
$prenom = $_SESSION['patient_data']['prenom'];
$secu = $_SESSION['couverture_sociale_data']['numero_secu'];
$date_admission = $_SESSION['hospitalisation_data']['date_hospitalisation'];
$date_admission_fr = DateTime::createFromFormat('Y-m-d', $date_admission)->format('d/m/Y');
$heure_admission = $_SESSION['hospitalisation_data']['heure_hospitalisation'];
$medecin_id = $_SESSION['hospitalisation_data']['nom_medecin'];

// Récupérer le nom du médecin
$sql1 = "SELECT p.nom FROM personnel p WHERE p.id_personnel = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("s", $medecin_id);
$stmt1->execute();
$stmt1->bind_result($medecin_nom);
$stmt1->fetch();
$stmt1->close();

// Récupérer le service du médecin
$sql2 = "SELECT s.libelle FROM personnel p 
         JOIN metier m ON p.id_metier = m.id_metier 
         JOIN service s ON m.id_service = s.id_service 
         WHERE p.nom = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("s", $medecin_nom);
$stmt2->execute();
$stmt2->bind_result($service);
$stmt2->fetch();
$stmt2->close();

if (!$service) {
    $service = 'Non spécifié';
}

$pdf = new PDF();
$pdf->AddPage();




$pdf->Ln(25);
$pdf->SetFont('Arial', '', 12); // Définissez la police et la taille si nécessaire
$pdf->MultiCell(0, 10, mb_convert_encoding("M/Mme $nom $prenom, la clinique LPFS a le plaisir de vous confirmer votre rendez-vous. Veuillez trouver ci-dessous les informations relatives à votre rendez-vous, ainsi que votre numéro de sécurité sociale.", 'ISO-8859-1', 'UTF-8'), 0, 'C');



$pdf->Ln(15);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, mb_convert_encoding("Numéro de sécurité sociale : $secu", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$pdf->Cell(0, 10, mb_convert_encoding("Rendez-vous avec le Dr. $medecin_nom", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$pdf->Cell(0, 10, mb_convert_encoding("Le $date_admission_fr à $heure_admission", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$pdf->Cell(0, 10, mb_convert_encoding("Au service : $service", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

$pdf->Ln(18);
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 10, mb_convert_encoding("Si vous souhaitez reporter ou annuler le rdv, appelez  minimum 3 jours avant la date du rdv ce numéro : 06 74 96 12 54", 'ISO-8859-1', 'UTF-8'), 0, 'C');

$pdf->Ln(20);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, mb_convert_encoding("Merci de vous présenter à l'accueil 15 minutes avant votre rendez-vous.", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

// Télécharger le PDF
$pdf->Output('D', mb_convert_encoding("Fiche_RDV_{$nom}_{$prenom}_{$date_admission_fr}.pdf", 'ISO-8859-1', 'UTF-8'));
?>
