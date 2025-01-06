<?php
/**********************************************
 * updatePost.php
 *
 * Endpoint to handle PUT request to update
 * an existing post in the 'posts' table using PDO.
 * It expects 'id', 'title', and 'content' in the request body.
 **********************************************/

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

// 3. Check if the request method is PUT
if ($_SERVER["REQUEST_METHOD"] === "PUT") {

    // 4. Ensure Content-Type is application/json
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    if (strpos($contentType, 'application/json') !== false) {
        // 5. Parse the JSON input
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON format."]);
            exit;
        }

        // 6. Retrieve 'id', 'title', and 'content' from the decoded JSON
        $id      = isset($decoded['id'])      ? $decoded['id']      : null;
        $title   = isset($decoded['title'])   ? $decoded['title']   : null;
        $content = isset($decoded['content']) ? $decoded['content'] : null;

    } else {
        // 7. If not JSON, respond with an error
        http_response_code(400);
        echo json_encode(["error" => "Content-Type must be application/json."]);
        exit;
    }

    // 8. Validate the inputs
    if (empty($id) || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "A valid 'id' is required."]);
        exit;
    }

    if (empty($title) || empty($content)) {
        http_response_code(400);
        echo json_encode(["error" => "Both 'title' and 'content' are required."]);
        exit;
    }

    $id = (int)$id; // Ensure $id is an integer

    // 9. Prepare the SQL UPDATE statement
    $sql = "UPDATE posts SET title = :title, content = :content WHERE id = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':title',   $title,   PDO::PARAM_STR);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);
        $stmt->bindValue(':id',      $id,      PDO::PARAM_INT);

        // 10. Execute the statement
        $stmt->execute();

        // 11. Check if any row was updated
        if ($stmt->rowCount() > 0) {
            // Successfully updated
            http_response_code(200);
            echo json_encode([
                "message" => "Post with ID $id has been updated successfully."
            ]);
        } else {
            // No rows updated (possibly invalid id or no change in data)
            http_response_code(404);
            echo json_encode([
                "error" => "No post found with ID $id or no changes made."
            ]);
        }

    } catch (PDOException $e) {
        // 12. Handle any SQL errors
        http_response_code(500);
        echo json_encode([
            "error" => "Error updating post: " . $e->getMessage()
        ]);
    }

} else {
    // 13. If not a PUT request, respond with an error
    http_response_code(405);
    echo json_encode([
        "error" => "Method not allowed. Only PUT is supported."
    ]);
}

// 14. Close the PDO connection (optional)
$pdo = null;
?>
