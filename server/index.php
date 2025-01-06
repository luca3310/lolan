<?php
header('Content-Type: application/json');

$host = 'db';  // Docker container navn
$dbname = 'my_database';
$username = 'user';
$password = 'user_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Hent alle tabeller
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $database_info = [];
    
    // For hver tabel, hent struktur
    foreach ($tables as $table) {
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $database_info[$table] = [
            'columns' => $columns,
            'row_count' => $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn()
        ];
    }
    
    echo json_encode([
        'message' => 'Database struktur',
        'status' => 'success',
        'tables' => $database_info
    ], JSON_PRETTY_PRINT);
    
} catch(PDOException $e) {
    echo json_encode([
        'message' => 'Database fejl',
        'error' => $e->getMessage(),
        'status' => 'error'
    ]);
}
?>
