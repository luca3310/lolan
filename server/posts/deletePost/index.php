<?php
/**********************************************
 * deletePost.php
 *
 * Endpoint to DELETE a post by its ID from the
 * 'posts' table using PDO, accepting 'id' as a
 * query parameter.
 **********************************************/

// Include the authentication component
require_once '../../libs/bearerChecker.php';
requireBearerAuth('password');   

// 1. Database connection info (Docker Compose environment)
$dsn      = "mysql:host=db;port=3306;dbname=my_database;charset=utf8mb4";
$db_user  = "user";
$db_pass  = "user_password";

try {
    // 2. Create a new PDO instance
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

// 3. Check HTTP method
if ($_SERVER["REQUEST_METHOD"] === "DELETE") {

    // 4. Retrieve 'id' from query parameter
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
    } else {
        http_response_code(400);
        echo json_encode(["error" => "A valid 'id' query parameter is required."]);
        exit;
    }

    // 5. Prepare the SQL DELETE statement
    $sql = "DELETE FROM posts WHERE id = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        // 6. Execute the statement
        $stmt->execute();

        // 7. Check how many rows were affected
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode([
                "message" => "Post with ID $id has been deleted successfully."
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "error" => "No post found with ID $id."
            ]);
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Error deleting post: " . $e->getMessage()
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode([
        "error" => "Method not allowed. Only DELETE is supported."
    ]);
}

$pdo = null;
?>
