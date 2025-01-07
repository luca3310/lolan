<?php
/**********************************************
 * deletePost.php
 *
 * Endpoint to DELETE a post by its ID from the
 * 'posts' table using PDO, accepting 'id' in the request body.
 **********************************************/

// Include the authentication component
require_once '../libs/bearerChecker.php';
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

    // 4. Determine Content-Type and parse input accordingly
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    if (strpos($contentType, 'application/json') !== false) {
        // Handle JSON input
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON format."]);
            exit;
        }

        $id = isset($decoded['id']) ? $decoded['id'] : null;

    } else {
        // Handle form data or other content types if necessary
        // For this example, we'll require JSON
        http_response_code(400);
        echo json_encode(["error" => "Content-Type must be application/json."]);
        exit;
    }

    // 5. Validate 'id'
    if (empty($id) || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "A valid 'id' is required in the request body."]);
        exit;
    }

    $id = (int)$id;

    // 6. Prepare the SQL DELETE statement
    $sql = "DELETE FROM posts WHERE id = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        // 7. Execute
        $stmt->execute();

        // 8. Check how many rows were affected
        if ($stmt->rowCount() > 0) {
            // Successfully deleted
            http_response_code(200);
            echo json_encode([
                "message" => "Post with ID $id has been deleted successfully."
            ]);
        } else {
            // No rows deleted => invalid id?
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
    // 9. Non-DELETE method => 405 Method Not Allowed
    http_response_code(405);
    echo json_encode([
        "error" => "Method not allowed. Only DELETE is supported."
    ]);
}

// 10. Optional: close PDO connection (it closes automatically at script end)
$pdo = null;
?>
