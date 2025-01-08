<?php
function patchPost($pdo) {
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
        
        // For PATCH accepterer vi kun content
        if (!isset($decoded['content'])) {
            http_response_code(400);
            echo json_encode(["error" => "Content er påkrævet for PATCH."]);
            return;
        }
        
        $content = $decoded['content'];
    } else {
        $content = isset($_POST['content']) ? $_POST['content'] : null;
    }
    
    if (empty($content)) {
        http_response_code(400);
        echo json_encode(["error" => "Content må ikke være tom."]);
        return;
    }
    
    $id = (int)$_GET['id'];
    
    try {
        // Først tjek om posten eksisterer og hent den nuværende titel
        $stmt = $pdo->prepare("SELECT title FROM posts WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $post = $stmt->fetch();
        
        if (!$post) {
            http_response_code(404);
            echo json_encode(["error" => "Ingen post fundet med ID $id."]);
            return;
        }

        // Opdater kun content og behold den eksisterende titel
        $stmt = $pdo->prepare("UPDATE posts SET content = :content WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);
        $stmt->execute();
        
        echo json_encode([
            "message" => "Post indhold opdateret succesfuldt.",
            "id" => $id
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Fejl ved opdatering af post: " . $e->getMessage()]);
    }
} 