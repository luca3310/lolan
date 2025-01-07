<?php

http_response_code(404);

/**
 * auth.php
 *
 * Provides functions for Bearer token authentication.
 */

/**
 * Checks for a valid Bearer token in the Authorization header.
 * If authentication fails, sends a 401 Unauthorized response and exits.
 *
 * @param string $expectedToken The token expected for successful authentication.
 */
function requireBearerAuth($expectedToken) {
    $authHeader = null;
    
    // Attempt to retrieve the Authorization header
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $authHeader = trim($headers['Authorization']);
        }
    }

    // Validate the Bearer token
    if ($authHeader !== 'Bearer ' . $expectedToken) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }
}
