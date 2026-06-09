<?php
// Headers CORS complets pour Chrome
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Gérer la requête OPTIONS (pre-flight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=rh_paie_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Erreur base de données"
    ]);
    exit();
}

// Lire les données
$input = file_get_contents("php://input");
$data = json_decode($input);

// Vérification simple - accepter admin/admin123
if ($data && isset($data->email) && isset($data->password)) {
    $email = $data->email;
    $password = $data->password;
    
    // Pour le test, accepter admin@rhpaie.com / admin123
    if ($email == 'admin@rhpaie.com' && $password == 'admin123') {
        echo json_encode([
            "success" => true,
            "message" => "Connexion réussie",
            "user" => [
                "id" => 1,
                "username" => "admin",
                "email" => "admin@rhpaie.com",
                "role" => "Administrateur"
            ],
            "token" => base64_encode("1_" . time())
        ]);
    } else {
        // Vérifier dans la base de données
        $sql = "SELECT u.*, r.Nom as role_nom 
                FROM Utilisateurs u 
                LEFT JOIN Rôle r ON u.IdRôle = r.Id 
                WHERE u.Email = :email";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($password == $user['Password']) {
                echo json_encode([
                    "success" => true,
                    "message" => "Connexion réussie",
                    "user" => [
                        "id" => (int)$user['Id'],
                        "username" => $user['UserName'],
                        "email" => $user['Email'],
                        "role" => $user['role_nom'] ?? "Employé"
                    ],
                    "token" => base64_encode($user['Id'] . "_" . time())
                ]);
            } else {
                echo json_encode([
                    "success" => false, 
                    "message" => "Mot de passe incorrect"
                ]);
            }
        } else {
            echo json_encode([
                "success" => false, 
                "message" => "Utilisateur non trouvé"
            ]);
        }
    }
} else {
    echo json_encode([
        "success" => false, 
        "message" => "Email et mot de passe requis"
    ]);
}
?>