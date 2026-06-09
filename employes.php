<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $query = "SELECT e.*, d.Nom as departement_nom 
                      FROM Employé e 
                      LEFT JOIN Departement d ON e.IdDepartement = d.Id 
                      WHERE e.Id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
            $employe = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($employe);
        } else {
            $query = "SELECT e.*, d.Nom as departement_nom 
                      FROM Employé e 
                      LEFT JOIN Departement d ON e.IdDepartement = d.Id 
                      ORDER BY e.Id DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $employes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($employes);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "INSERT INTO Employé (Matricule, Nom, PostNom, Prénom, Sexe, DateNaissance, 
                  Téléphone, Email, Poste, SalaireBase, Statut) 
                  VALUES (:matricule, :nom, :postNom, :prenom, :sexe, :dateNaissance, 
                  :telephone, :email, :poste, :salaireBase, :statut)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":matricule", $data->matricule);
        $stmt->bindParam(":nom", $data->nom);
        $stmt->bindParam(":postNom", $data->postNom);
        $stmt->bindParam(":prenom", $data->prenom);
        $stmt->bindParam(":sexe", $data->sexe);
        $stmt->bindParam(":dateNaissance", $data->dateNaissance);
        $stmt->bindParam(":telephone", $data->telephone);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":poste", $data->poste);
        $stmt->bindParam(":salaireBase", $data->salaireBase);
        $stmt->bindParam(":statut", $data->statut);
        
        if($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("success" => true, "message" => "Employé créé", "id" => $db->lastInsertId()));
        } else {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Erreur lors de la création"));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $id = $_GET['id'];
        
        $query = "UPDATE Employé SET Matricule = :matricule, Nom = :nom, PostNom = :postNom, 
                  Prénom = :prenom, Sexe = :sexe, DateNaissance = :dateNaissance, 
                  Téléphone = :telephone, Email = :email, Poste = :poste, 
                  SalaireBase = :salaireBase, Statut = :statut 
                  WHERE Id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":matricule", $data->matricule);
        $stmt->bindParam(":nom", $data->nom);
        $stmt->bindParam(":postNom", $data->postNom);
        $stmt->bindParam(":prenom", $data->prenom);
        $stmt->bindParam(":sexe", $data->sexe);
        $stmt->bindParam(":dateNaissance", $data->dateNaissance);
        $stmt->bindParam(":telephone", $data->telephone);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":poste", $data->poste);
        $stmt->bindParam(":salaireBase", $data->salaireBase);
        $stmt->bindParam(":statut", $data->statut);
        
        if($stmt->execute()) {
            echo json_encode(array("success" => true, "message" => "Employé modifié"));
        } else {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Erreur lors de la modification"));
        }
        break;
        
    case 'DELETE':
        $id = $_GET['id'];
        $query = "DELETE FROM Employé WHERE Id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if($stmt->execute()) {
            echo json_encode(array("success" => true, "message" => "Employé supprimé"));
        } else {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Erreur lors de la suppression"));
        }
        break;
}
?>