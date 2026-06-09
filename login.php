<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

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
        "message" => "Erreur base de données: " . $e->getMessage()
    ]);
    exit();
}

// Lire les données
$input = file_get_contents("php://input");
$data = json_decode($input);

if (!$data || !isset($data->email) || !isset($data->password)) {
    echo json_encode([
        "success" => false, 
        "message" => "Email et mot de passe requis"
    ]);
    exit();
}

$email = trim($data->email);
$password = $data->password;

// Chercher l'utilisateur
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
                "id" => (int)$user['Id'],  // Convertir en entier !
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
?>