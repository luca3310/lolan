<?php
/**********************************************
 * getPosts.php
 *
 * Endpoint to fetch ONLY the titles from
 * the 'posts' table using PDO. It respects optional
 * 'limit', 'search', 'offset', and 'perPage' query parameters with pagination.
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
        // Build count query with optional search condition
        $sql = "SELECT COUNT(*) as total FROM posts";
        $conditions = [];
        $params = [];

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $conditions[] = "title LIKE :search";
            $params[':search'] = '%' . $_GET['search'] . '%';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->execute();
        $total = (int)$stmt->fetch()['total'];

        // Retrieve 'limit' and cap the total if necessary
        $limit_param = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : null;
        if ($limit_param !== null && $total > $limit_param) {
            $total = $limit_param;
        }

        // Dynamically set per_page using 'perPage' param, defaulting to 10
        $per_page = (isset($_GET['perPage']) && is_numeric($_GET['perPage']) && (int)$_GET['perPage'] > 0)
                    ? (int)$_GET['perPage'] 
                    : 10;

        // Check for an 'offset' parameter
        $offset_param = isset($_GET['offset']) && is_numeric($_GET['offset']) ? (int)$_GET['offset'] : null;

        if ($offset_param !== null) {
            // Use provided offset
            $offset = $offset_param;
            // Derive page number from offset for reference
            $page = max(floor($offset / $per_page) + 1, 1);
        } else {
            // Fall back to page-based pagination
            $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
            $offset = ($page - 1) * $per_page;
        }

        // Determine total pages based on capped total
        $total_pages = ($per_page > 0) ? ceil($total / $per_page) : 0;

        // Build the main SELECT query for fetching posts
        $sql = "SELECT title, id FROM posts";
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY id DESC";

        // Calculate how many items to fetch for the current page,
        // considering the overall limit.
        $items_to_fetch = $per_page;
        if ($limit_param !== null) {
            $max_possible = $limit_param - $offset;
            if ($max_possible < 0) {
                $max_possible = 0;
            }
            if ($max_possible < $per_page) {
                $items_to_fetch = $max_possible;
            }
        }

        // If no items to fetch, set empty results; otherwise, query the database.
        if ($items_to_fetch <= 0) {
            $titles = [];
        } else {
            $sql .= " LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $items_to_fetch, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            $stmt->execute();
            $titles = $stmt->fetchAll();
        }

        // Add link to each post
        $posts_with_links = array_map(function($post) {
            return [
                'title' => $post['title'],
                'id'    => $post['id'],
                'link'  => "http://localhost/api/posts/getPost/?id=" . $post['id']
            ];
        }, $titles);

        // Determine next/previous availability
        $has_next = ($offset + $per_page) < $total;
        $has_previous = $offset > 0;

        // Base URL for pagination links
        $base_url = "http://localhost/api/posts/getPosts/";

        // Build URL parameters common to both modes
        $url_params = [];
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $url_params['search'] = $_GET['search'];
        }
        if ($limit_param !== null) {
            $url_params['limit'] = $limit_param;
        }
        // Include dynamic perPage in URL params
        $url_params['perPage'] = $per_page;

        // Prepare next/previous links based on whether 'offset' was used
        if ($offset_param !== null) {
            $next_offset = $offset + $per_page;
            $prev_offset = max($offset - $per_page, 0);

            // Calculate page numbers for next/previous pages for reference
            $next_page_num = max(floor($next_offset / $per_page) + 1, 1);
            $prev_page_num = max(floor($prev_offset / $per_page) + 1, 1);

            $next_params = array_merge($url_params, [
                'offset' => $next_offset,
                'page'   => $next_page_num
            ]);
            $prev_params = array_merge($url_params, [
                'offset' => $prev_offset,
                'page'   => $prev_page_num
            ]);

            $next_page_link = $has_next ? $base_url . '?' . http_build_query($next_params) : null;
            $previous_page_link = $has_previous ? $base_url . '?' . http_build_query($prev_params) : null;
        } else {
            // Function to build page-based URLs
            function buildUrl($base, $params, $page) {
                $params['page'] = $page;
                return $base . '?' . http_build_query($params);
            }
            $next_page_link = $has_next ? buildUrl($base_url, $url_params, $page + 1) : null;
            $previous_page_link = $has_previous ? buildUrl($base_url, $url_params, $page - 1) : null;
        }

        // Prepare the final response
        $response = [
            'url' => "http://localhost/api/posts/getPost",
            'data' => $posts_with_links,
            'pagination' => [
                'current_page'  => $page,
                'total_pages'   => $total_pages,
                'total_items'   => $total,
                'per_page'      => $per_page,
                'has_next'      => $has_next,
                'has_previous'  => $has_previous,
                'next_page'     => $next_page_link,
                'previous_page' => $previous_page_link
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
