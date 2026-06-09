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
        $query = "SELECT p.*, e.Nom, e.Prénom, e.Matricule, CONCAT(e.Nom, ' ', e.Prénom) as employe_nom 
                  FROM Paiement p 
                  JOIN Employé e ON p.IdEmployé = e.Id 
                  ORDER BY p.DatePaiement DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($paiements);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "INSERT INTO Paiement (IdEmployé, Mois, MontantNet, ModePaiement, DatePaiement, StatutPaiement) 
                  VALUES (:employe, :mois, :montant, :mode, :date, :statut)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":employe", $data->idEmployé);
        $stmt->bindParam(":mois", $data->mois);
        $stmt->bindParam(":montant", $data->montantNet);
        $stmt->bindParam(":mode", $data->modePaiement);
        $stmt->bindParam(":date", $data->datePaiement);
        $stmt->bindParam(":statut", $data->statutPaiement);
        
        if($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("success" => true, "message" => "Paiement enregistré"));
        } else {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Erreur lors de l'enregistrement"));
        }
        break;
}
?>