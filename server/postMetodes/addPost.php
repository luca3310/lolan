<?php
/**********************************************
 * addPost.php
 *
 * Endpoint to handle POST request to create
 * a new post in the 'posts' table using PDO,
 * matching the new schema (title, content).
 **********************************************/

// Include the authentication component
require_once '../libs/bearerChecker.php';
requireBearerAuth('password');   

// 1. Database connection info: match Docker Compose environment
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
    die("Database connection failed: " . $e->getMessage());
}

// 3. Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 3a. Determine Content-Type and parse input accordingly
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    if (strpos($contentType, 'application/json') !== false) {
        // Handle JSON input
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            echo "Error: Received content contained invalid JSON!";
            exit;
        }

        $title   = isset($decoded['title'])   ? $decoded['title']   : null;
        $content = isset($decoded['content']) ? $decoded['content'] : null;

    } else {
        // Handle form data
        $title   = isset($_POST['title'])   ? $_POST['title']   : null;
        $content = isset($_POST['content']) ? $_POST['content'] : null;
    }

    // 3b. Simple validation checks
    if (empty($title) || empty($content)) {
        http_response_code(400);
        echo json_encode(["error" => "Both 'title' and 'content' are required in body."]);
        exit;
    }

    // 3c. Prepare the SQL statement to insert a new post
    $sql = "INSERT INTO posts (title, content)
            VALUES (:title, :content)";
    
    try {
        // 3d. Prepare and bind values
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':title',   $title,   PDO::PARAM_STR);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);
        
        // 3e. Execute the statement
        $stmt->execute();

        // 3f. Retrieve the ID of the inserted post
        $insertedId = $pdo->lastInsertId();

        // 3g. Success response
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            "message" => "Post created successfully.",
            "id"      => $insertedId
        ]);

    } catch (PDOException $e) {
        // 3h. If there's an insertion error, show message
        http_response_code(500);
        echo json_encode(["error" => "Error inserting post: " . $e->getMessage()]);
    }

} else {
    // 4. Handle non-POST requests
    http_response_code(405);
    echo json_encode(["error" => "This endpoint only accepts POST requests."]);
}

// 5. (Optional) Close the PDO connection (handled automatically at end-of-script)
$pdo = null;
?>
