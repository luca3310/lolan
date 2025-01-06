<?php
/**********************************************
 * getPosts.php
 *
 * Endpoint to fetch ONLY the titles from
 * the 'posts' table using PDO.
 **********************************************/

// 1. Database connection info (Docker Compose environment)
$dsn     = "mysql:host=db;port=3306;dbname=my_database;charset=utf8mb4";
$db_user = "user";
$db_pass = "user_password";

try {
    // 2. Create a new PDO instance
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // If connection fails, stop and return error
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

// 3. We only want GET requests
if ($_SERVER["REQUEST_METHOD"] === "GET") {

    try {
        // 4. Retrieve ONLY the 'title' column from all posts
        $sql = "SELECT title FROM posts ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        // 5. Fetch all titles
        $titles = $stmt->fetchAll();

        // 6. Return data as JSON
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($titles);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error fetching titles: " . $e->getMessage()]);
    }

} else {
    // 7. If not a GET request, respond with an error
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed. Only GET is supported."]);
}

// 8. Close the PDO connection (optional - it closes automatically at the end)
$pdo = null;
?>
