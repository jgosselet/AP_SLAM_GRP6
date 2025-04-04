<?php
session_start();
require('fpdf/fpdf.php');
require 'config.php';

if (!isset($_GET['id_hospitalisation'])) {
    die("ID de l'hospitalisation manquant.");
}

$id_hospitalisation = $_GET['id_hospitalisation'];
$conn->set_charset("utf8");

// Récupérer les informations de l'hospitalisation
$sql = "SELECT h.*, p.nom AS patient_nom, p.prenom AS patient_prenom, p.numero_secu, per.nom AS medecin_nom, s.libelle AS service 
        FROM hospitalisation h
        JOIN patient p ON h.id_patient = p.id_patient
        JOIN personnel per ON h.id_personnel = per.id_personnel
        JOIN metier m ON per.id_metier = m.id_metier
        JOIN service s ON m.id_service = s.id_service
        WHERE h.id_hospitalisation = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_hospitalisation);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    die("Aucune donnée trouvée pour cet ID.");
}

$date_admission_fr = DateTime::createFromFormat('Y-m-d', $data['date_hospitalisation'])->format('d/m/Y');

class PDF extends FPDF {
    function Header() {
        $this->Image('Image/logo.png', 150, 10, 50);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, mb_convert_encoding('CONFIRMATION DE RENDEZ-VOUS', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(10);
    }
}

$pdf = new PDF();
$pdf->AddPage();

$pdf->Ln(25);
$pdf->SetFont('Arial', '', 12); // Définissez la police et la taille si nécessaire
$pdf->MultiCell(0, 10, mb_convert_encoding("M/Mme {$data['patient_nom']} {$data['patient_prenom']}, la clinique LPFS a le plaisir de vous confirmer votre rendez-vous. Veuillez trouver ci-dessous les informations relatives à votre rendez-vous, ainsi que votre numéro de sécurité sociale.", 'ISO-8859-1', 'UTF-8'), 0, 'C');

$pdf->Ln(15);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, mb_convert_encoding("Numéro de sécurité sociale : {$data['numero_secu']}", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$pdf->Cell(0, 10, mb_convert_encoding("Rendez-vous avec le Dr. {$data['medecin_nom']}", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$pdf->Cell(0, 10, mb_convert_encoding("Le $date_admission_fr à {$data['heure_hospitalisation']}", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$pdf->Cell(0, 10, mb_convert_encoding("Au service : {$data['service']}", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

$pdf->Ln(18);
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 10, mb_convert_encoding("Si vous souhaitez reporter ou annuler le rdv, appelez  minimum 3 jours avant la date du rdv ce numéro : 06 74 96 12 54", 'ISO-8859-1', 'UTF-8'), 0, 'C');

$pdf->Ln(20);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, mb_convert_encoding("Merci de vous présenter à l'accueil 15 minutes avant votre rendez-vous.", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

$pdf->Output('D', mb_convert_encoding("Fiche_RDV_{$data['patient_nom']}_{$data['patient_prenom']}_$date_admission_fr.pdf", 'ISO-8859-1', 'UTF-8'));
?>