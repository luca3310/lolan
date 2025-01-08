<?php
function addPost($pdo) {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        
        if (!is_array($decoded)) {
            http_response_code(400);
            echo json_encode(["error" => "Ugyldigt JSON format"]);
            return;
        }
        
        $title = isset($decoded['title']) ? $decoded['title'] : null;
        $content = isset($decoded['content']) ? $decoded['content'] : null;
    } else {
        $title = isset($_POST['title']) ? $_POST['title'] : null;
        $content = isset($_POST['content']) ? $_POST['content'] : null;
    }
    
    if (empty($title) || empty($content)) {
        http_response_code(400);
        echo json_encode(["error" => "Både 'title' og 'content' er påkrævet."]);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content) VALUES (:title, :content)");
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);
        $stmt->execute();
        
        $insertedId = $pdo->lastInsertId();

        // Hent den nyoprettede post
        $stmt = $pdo->prepare("SELECT id, title, content FROM posts WHERE id = :id");
        $stmt->bindValue(':id', $insertedId, PDO::PARAM_INT);
        $stmt->execute();
        $newPost = $stmt->fetch();
        
        http_response_code(201);
        header('Content-Type: application/json; charset=utf-8');
        header('Location: /api/posts/?id=' . $insertedId);
        echo json_encode([
            "next" => "/api/posts/?id=" . $insertedId,
            "message" => "Post oprettet succesfuldt.",
            "post" => [
                "id" => $newPost['id'],
                "title" => $newPost['title'],
                "content" => $newPost['content']
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Fejl ved oprettelse af post: " . $e->getMessage()]);
    }
} 