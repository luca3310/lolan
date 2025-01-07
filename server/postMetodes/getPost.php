<?php
/**********************************************
 * getPost.php
 *
 * Endpoint to handle POST request to retrieve
 * the 'title' and 'content' of a specific post
 * from the 'posts' table using PDO.
 * It expects 'id' in the request body as JSON.
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

// 3. Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

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

        // 6. Retrieve 'id' from the decoded JSON
        $id = isset($decoded['id']) ? $decoded['id'] : null;

    } else {
        // 7. If not JSON, respond with an error
        http_response_code(400);
        echo json_encode(["error" => "Content-Type must be application/json."]);
        exit;
    }

    // 8. Validate the 'id'
    if (empty($id) || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "A valid 'id' is required in the request body."]);
        exit;
    }

    $id = (int)$id; // Ensure $id is an integer

    // 9. Prepare the SQL SELECT statement to retrieve 'title' and 'content'
    $sql = "SELECT title, content FROM posts WHERE id = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        // 10. Execute the statement
        $stmt->execute();

        // 11. Fetch the post
        $post = $stmt->fetch();

        if ($post) {
            // 12. Success response with 'title' and 'content'
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "url"     => "http://localhost/api/getMetodes/getPosts.php"
                "id"      => $id,
                "title"   => $post['title'],
                "content" => $post['content']
            ]);
        } else {
            // 13. Post not found
            http_response_code(404);
            echo json_encode([
                "error" => "No post found with ID $id."
            ]);
        }

    } catch (PDOException $e) {
        // 14. Handle any SQL errors
        http_response_code(500);
        echo json_encode([
            "error" => "Error retrieving post: " . $e->getMessage()
        ]);
    }

} else {
    // 15. If not a POST request, respond with an error
    http_response_code(405);
    echo json_encode([
        "error" => "Method not allowed. Only POST is supported."
    ]);
}

// 16. Close the PDO connection (optional)
$pdo = null;
?>
