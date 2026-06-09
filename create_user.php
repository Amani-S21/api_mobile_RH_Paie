<?php
header("Content-Type: text/html; charset=UTF-8");

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Création utilisateur RH</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: auto; }
        .success { color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>🔐 Création de l'utilisateur admin</h1>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Mot de passe à utiliser
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<div class='info'>";
    echo "<strong>📝 Informations :</strong><br>";
    echo "Email: admin@rhpaie.com<br>";
    echo "Mot de passe: " . $password . "<br>";
    echo "Hash généré: <code>" . htmlspecialchars($hash) . "</code><br>";
    echo "</div>";
    
    // Supprimer l'ancien utilisateur
    $deleteQuery = "DELETE FROM Utilisateurs WHERE Email = 'admin@rhpaie.com'";
    $db->exec($deleteQuery);
    echo "<div class='info'>✅ Ancien utilisateur supprimé</div>";
    
    // Créer le nouvel utilisateur
    $insertQuery = "INSERT INTO Utilisateurs (UserName, Email, Password, IdRôle) 
                    VALUES ('admin', 'admin@rhpaie.com', :hash, 1)";
    
    $stmt = $db->prepare($insertQuery);
    $stmt->bindParam(":hash", $hash);
    
    if ($stmt->execute()) {
        echo "<div class='success'>";
        echo "✅ Utilisateur créé avec succès !<br>";
        echo "ID: " . $db->lastInsertId() . "<br>";
        echo "Email: admin@rhpaie.com<br>";
        echo "Mot de passe: admin123<br>";
        echo "</div>";
    } else {
        echo "<div class='error'>❌ Erreur lors de la création de l'utilisateur</div>";
    }
    
    // Vérifier l'utilisateur créé
    $checkQuery = "SELECT Id, UserName, Email, IdRôle FROM Utilisateurs WHERE Email = 'admin@rhpaie.com'";
    $result = $db->query($checkQuery);
    
    if ($result->rowCount() > 0) {
        echo "<h2>📋 Vérification de l'utilisateur</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Rôle ID</th></tr>";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['Id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['UserName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
            echo "<td>" . $row['IdRôle'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Tester la vérification du mot de passe
    echo "<h2>🧪 Test de vérification du mot de passe</h2>";
    
    $testQuery = "SELECT Password FROM Utilisateurs WHERE Email = 'admin@rhpaie.com'";
    $testResult = $db->query($testQuery);
    $storedHash = $testResult->fetch(PDO::FETCH_ASSOC)['Password'];
    
    if (password_verify('admin123', $storedHash)) {
        echo "<div class='success'>✅ Test réussi : password_verify() fonctionne correctement !</div>";
    } else {
        echo "<div class='error'>❌ Test échoué : password_verify() ne fonctionne pas</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Erreur base de données: " . $e->getMessage() . "</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Erreur: " . $e->getMessage() . "</div>";
}

echo "
    <h2>🔧 Test de l'API de login</h2>
    <div class='info'>
        <button onclick='testLogin()'>Tester la connexion</button>
        <pre id='result'></pre>
    </div>
    
    <script>
    async function testLogin() {
        const resultDiv = document.getElementById('result');
        resultDiv.innerHTML = 'Connexion en cours...';
        
        try {
            const response = await fetch('http://localhost/api_rh/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: 'admin@rhpaie.com',
                    password: 'admin123'
                })
            });
            
            const data = await response.json();
            resultDiv.innerHTML = JSON.stringify(data, null, 2);
            
            if (data.success) {
                resultDiv.style.background = '#d4edda';
                resultDiv.style.color = 'green';
            } else {
                resultDiv.style.background = '#f8d7da';
                resultDiv.style.color = 'red';
            }
        } catch(e) {
            resultDiv.innerHTML = 'Erreur: ' + e.message;
            resultDiv.style.background = '#f8d7da';
            resultDiv.style.color = 'red';
        }
    }
    </script>
</body>
</html>";
?>