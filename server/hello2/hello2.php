<?php
header('Content-Type: application/json');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$host = 'mysql_server'; 
$db   = 'my_database';
$user = 'user';
$pass = 'user_password';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT 'Hello from MySQL!' as greeting");
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status'   => 'success',
            'database' => $row['greeting']
        ]);
    } else {
        echo json_encode([
            'error' => 'Invalid request method'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
