<?php
/**********************************************
 * getPosts.php
 *
 * Endpoint to fetch ONLY the titles from
 * the 'posts' table using PDO. It respects optional
 * 'limit' and 'search' query parameters and requires
 * a valid Bearer token for authentication.
 **********************************************/

// Include the authentication component
require_once '../libs/bearerChecker.php';
requireBearerAuth('password');    // Use "password" as the expected token

// 1. Database connection info (Docker Compose environment)
$dsn     = "mysql:host=db;port=3306;dbname=my_database;charset=utf8mb4";
$db_user = "user";
$db_pass = "user_password";

try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

// 2. We only want GET requests
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    try {
        $sql = "SELECT title FROM posts";
        $conditions = [];
        $params = [];

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $conditions[] = "title LIKE :search";
            $params[':search'] = '%' . $_GET['search'] . '%';
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY id DESC";

        if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
            $limit = (int)$_GET['limit'];
            if ($limit > 0) {
                $sql .= " LIMIT " . $limit;
            }
        }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->execute();

        $titles = $stmt->fetchAll();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($titles);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error fetching titles: " . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed. Only GET is supported."]);
}

$pdo = null;
?>
