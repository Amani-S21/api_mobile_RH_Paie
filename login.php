<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Pour le développement, on accepte des identifiants par défaut
// admin@rhpaie.com / admin123

if(!empty($data->email) && !empty($data->password)) {
    $query = "SELECT u.*, r.Nom as role_nom FROM Utilisateurs u 
              LEFT JOIN Rôle r ON u.IdRôle = r.Id 
              WHERE u.Email = :email";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $data->email);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Pour le développement, on accepte le mot de passe 'admin123' ou le hash
        if($data->password == 'admin123' || password_verify($data->password, $user['Password'])) {
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Connexion réussie",
                "user" => array(
                    "id" => $user['Id'],
                    "username" => $user['UserName'],
                    "email" => $user['Email'],
                    "role" => $user['role_nom']
                ),
                "token" => base64_encode($user['Id'] . "_" . time())
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("success" => false, "message" => "Mot de passe incorrect"));
        }
    } else {
        // Pour le développement, on crée un utilisateur par défaut si non existant
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insertQuery = "INSERT INTO Utilisateurs (UserName, Email, Password, IdRôle) VALUES ('admin', 'admin@rhpaie.com', :password, 1)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(":password", $hashedPassword);
        $insertStmt->execute();
        
        http_response_code(200);
        echo json_encode(array(
            "success" => true,
            "message" => "Compte admin créé, connexion réussie",
            "user" => array(
                "id" => $db->lastInsertId(),
                "username" => "admin",
                "email" => "admin@rhpaie.com",
                "role" => "Administrateur"
            ),
            "token" => base64_encode($db->lastInsertId() . "_" . time())
        )); 
    }
} else {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Email et mot de passe requis"));
}
?>