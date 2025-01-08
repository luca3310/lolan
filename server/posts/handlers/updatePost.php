<?php
function updatePost($pdo) {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Et gyldigt 'id' parameter er påkrævet."]);
        return;
    }

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
    
    $id = (int)$_GET['id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE posts SET title = :title, content = :content WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "Post opdateret succesfuldt."]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Ingen post fundet med ID $id."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Fejl ved opdatering af post: " . $e->getMessage()]);
    }
} 