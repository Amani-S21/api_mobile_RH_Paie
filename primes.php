<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.*, e.Nom, e.Prénom, CONCAT(e.Nom, ' ', e.Prénom) as employe_nom 
          FROM Primes p 
          JOIN Employé e ON p.IdEmployé = e.Id";
$stmt = $db->prepare($query);
$stmt->execute();
$primes = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($primes);
?>