<?php
function getPosts($pdo) {
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

        // Håndter limit parameter
        $limit_param = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : null;
        if ($limit_param !== null && $total > $limit_param) {
            $total = $limit_param;
        }

        $per_page = (isset($_GET['perPage']) && is_numeric($_GET['perPage']) && (int)$_GET['perPage'] > 0)
                    ? (int)$_GET['perPage'] 
                    : 10;

        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $offset = ($page - 1) * $per_page;

        $total_pages = ceil($total / $per_page);

        $sql = "SELECT title, id FROM posts";
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY id DESC";

        // Beregn hvor mange items der skal hentes
        $items_to_fetch = $per_page;
        if ($limit_param !== null) {
            $remaining = $limit_param - $offset;
            if ($remaining < 0) {
                $remaining = 0;
            }
            if ($remaining < $per_page) {
                $items_to_fetch = $remaining;
            }
        }

        if ($items_to_fetch > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $items_to_fetch, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            $stmt->execute();
            $posts = $stmt->fetchAll();
        } else {
            $posts = [];
        }

        $posts_with_links = array_map(function($post) {
            return [
                'title' => $post['title'],
                'id'    => $post['id'],
                'link'  => "http://localhost/api/posts/?id=" . $post['id']
            ];
        }, $posts);

        // Beregn om der er næste og forrige sider
        $has_next = ($offset + $per_page) < $total;
        $has_previous = $offset > 0;

        // Byg basis URL for pagination links
        $base_url = "http://localhost/api/posts/";
        $url_params = [];

        // Tilføj søgeparameter hvis det findes
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $url_params['search'] = $_GET['search'];
        }

        // Tilføj limit parameter hvis det findes
        if ($limit_param !== null) {
            $url_params['limit'] = $limit_param;
        }

        // Tilføj perPage parameter hvis det er andet end standard
        if ($per_page !== 10) {
            $url_params['perPage'] = $per_page;
        }

        // Byg next og previous links
        $next_page_link = null;
        $previous_page_link = null;

        if ($has_next) {
            $next_params = array_merge($url_params, ['page' => $page + 1]);
            $next_page_link = $base_url . '?' . http_build_query($next_params);
        }

        if ($has_previous) {
            $prev_params = array_merge($url_params, ['page' => $page - 1]);
            $previous_page_link = $base_url . '?' . http_build_query($prev_params);
        }

        $response = [
            'data' => $posts_with_links,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_items' => $total,
                'per_page'    => $per_page,
                'has_next'    => $has_next,
                'has_previous' => $has_previous,
                'next_page'   => $next_page_link,
                'previous_page' => $previous_page_link
            ]
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Fejl ved hentning af posts: " . $e->getMessage()]);
    }
} 