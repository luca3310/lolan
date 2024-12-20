<?php 

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $name = $input['name'] ?? "world";

    echo json_encode(['name' => "hello, $name!"]);
    } else {
        echo json_encode(["error" => "Invalid requst method"]);
}
?>