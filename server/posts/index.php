<?php
/**********************************************
 * Posts API Endpoints
 * 
 * Dette er hovedfilen for alle posts-relaterede endpoints:
 * - GET /posts/ - Hent alle posts
 * - GET /posts/?id=X - Hent specifik post
 * - POST /posts/ - Opret ny post
 * - PUT /posts/?id=X - Opdater post
 * - PATCH /posts/?id=X - Opdater kun indhold
 * - DELETE /posts/?id=X - Slet post
 **********************************************/

// Inkluder authentication component
require_once '../libs/bearerChecker.php';

// Inkluder handlers
require_once 'handlers/getPosts.php';
require_once 'handlers/getPost.php';
require_once 'handlers/addPost.php';
require_once 'handlers/updatePost.php';
require_once 'handlers/patchPost.php';
require_once 'handlers/deletePost.php';

// Database forbindelses information
$dsn      = "mysql:host=db;port=3306;dbname=my_database;charset=utf8mb4";
$db_user  = "user";
$db_pass  = "user_password";

try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database forbindelse fejlede: " . $e->getMessage()]);
    exit;
}

// Håndter forskellige HTTP metoder
$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
    case "GET":
        if (isset($_GET['id'])) {
            getPost($pdo);
        } else {
            getPosts($pdo);
        }
        break;
        
    case "POST":
        requireBearerAuth('password');
        addPost($pdo);
        break;
        
    case "PUT":
        requireBearerAuth('password');
        updatePost($pdo);
        break;

    case "PATCH":
        requireBearerAuth('password');
        patchPost($pdo);
        break;
        
    case "DELETE":
        requireBearerAuth('password');
        deletePost($pdo);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["error" => "Metode ikke tilladt"]);
        break;
}

// Luk database forbindelsen
$pdo = null; 