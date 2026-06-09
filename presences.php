<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $date = isset($_GET['date']) ? date('Y-m-d', strtotime($_GET['date'])) : date('Y-m-d');
        
        $query = "SELECT p.*, e.Nom, e.Prénom, e.Matricule, CONCAT(e.Nom, ' ', e.Prénom) as employe_nom 
                  FROM Présence p 
                  RIGHT JOIN Employé e ON p.IdEmployé = e.Id AND p.Date = :date
                  ORDER BY e.Nom ASC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        $presences = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($presences);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        $checkQuery = "SELECT Id FROM Présence WHERE IdEmployé = :employe AND Date = :date";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(":employe", $data->idEmployé);
        $checkStmt->bindParam(":date", $data->date);
        $checkStmt->execute();
        
        if($checkStmt->rowCount() > 0) {
            $query = "UPDATE Présence SET HeureArrivée = :arrivee, HeureSortie = :sortie, Statut = :statut 
                      WHERE IdEmployé = :employe AND Date = :date";
        } else {
            $query = "INSERT INTO Présence (IdEmployé, Date, HeureArrivée, HeureSortie, Statut) 
                      VALUES (:employe, :date, :arrivee, :sortie, :statut)";
        }
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":employe", $data->idEmployé);
        $stmt->bindParam(":date", $data->date);
        $stmt->bindParam(":arrivee", $data->heureArrivée);
        $stmt->bindParam(":sortie", $data->heureSortie);
        $stmt->bindParam(":statut", $data->statut);
        
        if($stmt->execute()) {
            echo json_encode(array("success" => true, "message" => "Présence enregistrée"));
        } else {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Erreur"));
        }
        break;
}
?>