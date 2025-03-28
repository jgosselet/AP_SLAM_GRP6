<?php
session_start();

if (!isset($_GET['numero_secu'])) {
    echo json_encode(['success' => false, 'message' => 'Numéro de sécurité sociale manquant.']);
    exit;
}

$numero_secu = trim($_GET['numero_secu']);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=cliniquelpfs', 'root', 'sio2024');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT 
            p.nom, p.prenom, p.civilite, p.date_naissance, p.adresse, p.cp AS code_postal, p.ville, p.email, p.tel AS telephone,
            proche_p.nom AS nom_personne_p, proche_p.prenom AS prenom_personne_p, proche_p.tel AS tel_personne_p, proche_p.adresse AS adresse_personne_p,
            proche_c.nom AS nom_personne_c, proche_c.prenom AS prenom_personne_c, proche_c.tel AS tel_personne_c, proche_c.adresse AS adresse_personne_c,
            cs.numero_secu, cs.organisme, cs.patient_assurance, cs.patient_ald, cs.nom_mutuelle, cs.num_adherent
        FROM 
            patient AS p
        LEFT JOIN proche AS proche_p ON p.id_personne_prevenir = proche_p.id_personne
        LEFT JOIN proche AS proche_c ON p.id_personne_confiance = proche_c.id_personne
        LEFT JOIN couverture_sociale AS cs ON p.numero_secu = cs.numero_secu
        WHERE 
            p.numero_secu = :numero_secu
        LIMIT 1
    ");
    $stmt->execute([':numero_secu' => $numero_secu]);

    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($patient) {
        // Stocker les données dans une variable de session
        $_SESSION['patient_data'] = $patient;

        echo json_encode(['success' => true, 'patient' => $patient]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aucun patient trouvé avec ce numéro de sécurité sociale.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données.']);
    exit;
}
?>
