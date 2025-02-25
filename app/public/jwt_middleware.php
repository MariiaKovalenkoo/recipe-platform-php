<?php
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function checkJwtMiddleware(): void
{
    $headers = apache_request_headers();
    //error_log("Incoming Headers: " . print_r($headers, true), 3, __DIR__ . '/../error_log.log'); // Log headers

    $authHeader = $headers['Authorization'] ?? null;

    if (!$authHeader) {
        error_log("JWT Middleware Error: No Authorization header found", 3, __DIR__ . '/../error_log.log');
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. No token provided."));
        exit;
    }

    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if (!$jwt) {
        error_log("JWT Middleware Error: Invalid token format", 3, __DIR__ . '/../error_log.log');
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. Invalid token format."));
        exit;
    }

    try {
        $secret_key = "secret_key";
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

        if (!isset($decoded->data)) {
            error_log("JWT Middleware Error: Decoded token has no 'data' field", 3, __DIR__ . '/../error_log.log');
            http_response_code(401);
            echo json_encode(array("message" => "Access denied. Invalid token data."));
            exit;
        }

        $GLOBALS['current_user'] = $decoded->data;

        // Log decoded token and current user data
        //error_log("Decoded Token: " . print_r($decoded, true), 3, __DIR__ . '/../error_log.log');
        //error_log("JWT Middleware: User ID set to " . $GLOBALS['current_user']->id, 3, __DIR__ . '/../error_log.log');

    } catch (Exception $e) {
        error_log("JWT Middleware Error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. " . $e->getMessage()));
        exit;
    }
}


