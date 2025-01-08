<?php
function getPost($pdo) {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Et gyldigt 'id' parameter er påkrævet."]);
        return;
    }

    $id = (int)$_GET['id'];
    
    try {
        $stmt = $pdo->prepare("SELECT title, content FROM posts WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $post = $stmt->fetch();
        
        if ($post) {
            // Byg prev URL med eventuelle eksisterende parametre
            $prev_url = "http://localhost/api/posts/";
            $url_params = [];
            
            // Bevar søgeparameter hvis det findes
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $url_params['search'] = $_GET['search'];
            }
            
            // Bevar page parameter hvis det findes
            if (isset($_GET['page']) && is_numeric($_GET['page'])) {
                $url_params['page'] = (int)$_GET['page'];
            }
            
            // Bevar perPage parameter hvis det findes
            if (isset($_GET['perPage']) && is_numeric($_GET['perPage'])) {
                $url_params['perPage'] = (int)$_GET['perPage'];
            }
            
            // Tilføj parametre til URL'en hvis der er nogen
            if (!empty($url_params)) {
                $prev_url .= '?' . http_build_query($url_params);
            }

            header('Content-Type: application/json; charset=utf-8');
            http_response_code(200);
            echo json_encode([
                "prev"    => $prev_url,
                "id"      => $id,
                "title"   => $post['title'],
                "content" => $post['content']
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Ingen post fundet med ID $id."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Fejl ved hentning af post: " . $e->getMessage()]);
    }
} 