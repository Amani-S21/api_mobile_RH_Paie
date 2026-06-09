<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$stats = array();

// Nombre d'employés
$query = "SELECT COUNT(*) as total, SUM(CASE WHEN Statut = 'Actif' THEN 1 ELSE 0 END) as actifs FROM Employé";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['employes'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Congés
$query = "SELECT COUNT(*) as total, 
          SUM(CASE WHEN Statut = 'En attente' THEN 1 ELSE 0 END) as en_attente,
          SUM(CASE WHEN Statut = 'Approuvé' THEN 1 ELSE 0 END) as approuves
          FROM Congé";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['conges'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Paiements
$query = "SELECT COUNT(*) as total, SUM(MontantNet) as total_montant FROM Paiement WHERE StatutPaiement = 'Payé'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['paiements'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Présences du jour
$today = date('Y-m-d');
$query = "SELECT COUNT(*) as presents FROM Présence WHERE Date = :date AND Statut = 'Présent'";
$stmt = $db->prepare($query);
$stmt->bindParam(":date", $today);
$stmt->execute();
$stats['presences'] = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($stats);
?>