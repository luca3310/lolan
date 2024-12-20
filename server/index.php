<?php 

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === 'GET') {
    
    echo json_encode(['name' => "hello, world!"]);
    } else {
        echo json_encode(["error" => "Invalid requst method"]);
}
?>