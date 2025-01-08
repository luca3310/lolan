<?php
function deletePost($pdo) {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Et gyldigt 'id' parameter er påkrævet."]);
        return;
    }

    $id = (int)$_GET['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "Post slettet succesfuldt."]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Ingen post fundet med ID $id."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Fejl ved sletning af post: " . $e->getMessage()]);
    }
} 