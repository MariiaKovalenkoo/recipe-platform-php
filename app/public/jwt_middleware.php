<?php
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function checkJwtMiddleware()
{
    // Check for the 'Authorization' header
    $headers = apache_request_headers();
    // error_log(print_r($headers, true), 3, __DIR__ . '/../error_log.log'); // Log the input data
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    if (!$authHeader) {
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. No token provided."));
        exit;
    }

    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt) {
        try {
            // Your secret key goes here
            $secret_key = "fancy_key"; // Make sure to use a secure and unique key.

            // Decode the JWT - assumes the JWT package from Firebase is being used
            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

            // The token is valid, you can add the decoded data to a global variable or pass it along if necessary
            // For example:
            $GLOBALS['current_user'] = $decoded->data;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(array("message" => "Access denied. " . $e->getMessage()));
            exit;
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. Invalid token."));
        exit;
    }
}


