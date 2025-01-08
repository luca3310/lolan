<?php
/**********************************************
 * getPosts.php
 *
 * Endpoint to fetch ONLY the titles from
 * the 'posts' table using PDO. It respects optional
 * 'limit' and 'search' query parameters.
 **********************************************/

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
        $sql = "SELECT COUNT(*) as total FROM posts";
        $conditions = [];
        $params = [];

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $conditions[] = "title LIKE :search";
            $params[':search'] = '%' . $_GET['search'] . '%';
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->execute();
        $total = $stmt->fetch()['total'];

        // Pagination med fast antal per side
        $per_page = 10; // Fast grænse på 5 posts per side
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $per_page;

        $sql = "SELECT title, id FROM posts";
        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY id DESC";

        if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
            $limit = (int)$_GET['limit'];
            if ($limit > 0) {
                $sql .= " LIMIT " . $limit;
                $stmt = $pdo->prepare($sql);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
                $stmt->execute();
                $titles = $stmt->fetchAll();
                
                $response = [
                    'data' => $titles
                ];
                
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($response);
                exit;
            }
        }

        // Hvis ingen limit er angivet, brug pagination
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->execute();

        $titles = $stmt->fetchAll();
        
        // Tilføj link til hver post
        $posts_with_links = array_map(function($post) {
            return [
                'title' => $post['title'],
                'id' => $post['id'],
                'link' => "http://localhost/api/posts/getPost/?id=" . $post['id']
            ];
        }, $titles);

        $total_pages = ceil($total / $per_page);
        $has_next = $page < $total_pages;
        $has_previous = $page > 1;

        // Byg basis URL
        $base_url = "http://localhost/api/posts/getPosts/";
        
        // Tilføj søgeparameter hvis det findes
        $url_params = [];
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $url_params['search'] = $_GET['search'];
        }
        if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
            $url_params['limit'] = (int)$_GET['limit'];
        }

        // Funktion til at bygge URL med parametre
        function buildUrl($base, $params, $page) {
            $params['page'] = $page;
            return $base . '?' . http_build_query($params);
        }

        $response = [
            'url' => "http://localhost/api/posts/getPost",
            'data' => $posts_with_links,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_items' => $total,
                'per_page' => $per_page,
                'has_next' => $has_next,
                'has_previous' => $has_previous,
                'next_page' => $has_next ? buildUrl($base_url, $url_params, $page + 1) : null,
                'previous_page' => $has_previous ? buildUrl($base_url, $url_params, $page - 1) : null
            ]
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);

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
