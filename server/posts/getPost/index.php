<?php
/**********************************************
 * getPost.php
 *
 * Endpoint to handle GET request to retrieve
 * the 'title' and 'content' of a specific post
 * from the 'posts' table using PDO.
 * It expects 'id' as a query parameter.
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
    header('Content-Type: application/json');
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

// 3. Check if the request method is GET
if ($_SERVER["REQUEST_METHOD"] === "GET") {

    // 4. Retrieve 'id' from query parameter
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
    } else {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(["error" => "A valid 'id' query parameter is required."]);
        exit;
    }

    // 5. Prepare the SQL SELECT statement to retrieve 'title' and 'content'
    $sql = "SELECT title, content FROM posts WHERE id = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        // 6. Execute the statement
        $stmt->execute();

        // 7. Fetch the post
        $post = $stmt->fetch();

        if ($post) {
            // 8. Success response with 'title' and 'content'
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "prev"    => "http://localhost/api/posts/getPosts/",
                "id"      => $id,
                "title"   => $post['title'],
                "content" => $post['content']
            ]);
        } else {
            // 9. Post not found
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(["error" => "No post found with ID $id."]);
        }

    } catch (PDOException $e) {
        // 10. Handle any SQL errors
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Error retrieving post: " . $e->getMessage()]);
    }

} else {
    // 11. If not a GET request, respond with an error
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Method not allowed. Only GET is supported."]);
}

// 12. Close the PDO connection (optional)
$pdo = null;
?>
