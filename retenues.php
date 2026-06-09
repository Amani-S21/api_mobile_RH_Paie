<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM Retenues";
$stmt = $db->prepare($query);
$stmt->execute();
$retenues = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($retenues);
?>