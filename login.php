<?php
// Headers CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Gestion de la requête OPTIONS (pre-flight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inclusion de la connexion à la base de données
require_once 'config/database.php';

// Initialisation de la réponse
$response = array(
    "success" => false,
    "message" => ""
);

try {
    // Créer la connexion à la base de données
    $database = new Database();
    $db = $database->getConnection();
    
    // Récupérer les données envoyées par Flutter
    $data = json_decode(file_get_contents("php://input"));
    
    // Vérifier que les données sont complètes
    if (!isset($data->email) || !isset($data->password)) {
        $response["message"] = "Email et mot de passe requis";
        echo json_encode($response);
        exit();
    }
    
    $email = trim($data->email);
    $password = $data->password;
    
    // Valider l'email
    if (empty($email) || empty($password)) {
        $response["message"] = "Email et mot de passe ne peuvent pas être vides";
        echo json_encode($response);
        exit();
    }
    
    // Requête pour récupérer l'utilisateur
    $query = "SELECT u.*, r.Nom as role_nom 
              FROM Utilisateurs u 
              LEFT JOIN Rôle r ON u.IdRôle = r.Id 
              WHERE u.Email = :email 
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    // Vérifier si l'utilisateur existe
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérifier le mot de passe avec password_verify()
        // Le hash dans la base doit être généré avec password_hash()
        if (password_verify($password, $user['Password'])) {
            // Connexion réussie
            $response["success"] = true;
            $response["message"] = "Connexion réussie";
            $response["user"] = array(
                "id" => $user['Id'],
                "username" => $user['UserName'],
                "email" => $user['Email'],
                "role" => $user['role_nom']
            );
            // Générer un token simple (en production, utilisez JWT)
            $response["token"] = base64_encode($user['Id'] . "_" . time());
            
            http_response_code(200);
        } else {
            // Mot de passe incorrect
            $response["message"] = "Mot de passe incorrect";
            http_response_code(401);
        }
    } else {
        // Utilisateur non trouvé
        $response["message"] = "Utilisateur non trouvé";
        http_response_code(401);
    }
    
} catch (PDOException $e) {
    $response["message"] = "Erreur base de données: " . $e->getMessage();
    http_response_code(500);
} catch (Exception $e) {
    $response["message"] = "Erreur: " . $e->getMessage();
    http_response_code(500);
}

// Retourner la réponse en JSON
echo json_encode($response);
?>