<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT");

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $query = "SELECT c.*, e.Nom, e.Prénom, CONCAT(e.Nom, ' ', e.Prénom) as employe_nom, tc.Designation as type_conge 
                  FROM Congé c 
                  JOIN Employé e ON c.IdEmployé = e.Id 
                  JOIN TypeCongé tc ON c.IdTypeCongé = tc.Id 
                  ORDER BY c.DateDemande DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $conges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($conges);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "INSERT INTO Congé (IdTypeCongé, IdEmployé, Motif, DateDébut, DateFin, Statut) 
                  VALUES (:typeConge, :employe, :motif, :dateDebut, :dateFin, 'En attente')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":typeConge", $data->idTypeCongé);
        $stmt->bindParam(":employe", $data->idEmployé);
        $stmt->bindParam(":motif", $data->motif);
        $stmt->bindParam(":dateDebut", $data->dateDébut);
        $stmt->bindParam(":dateFin", $data->dateFin);
        
        if($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("success" => true, "message" => "Demande de congé envoyée"));
        } else {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Erreur lors de l'envoi"));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $id = $_GET['id'];
        
        $query = "UPDATE Congé SET Statut = :statut WHERE Id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":statut", $data->statut);
        
        if($stmt->execute()) {
            echo json_encode(array("success" => true, "message" => "Statut du congé mis à jour"));
        } else {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Erreur lors de la mise à jour"));
        }
        break;
}
?>