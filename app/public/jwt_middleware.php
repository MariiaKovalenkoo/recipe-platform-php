<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Services\UserService;

function checkJwtMiddleware(): void
{
//    error_log("\ncheckJwtMiddleware called for URI: " . $_SERVER['REQUEST_URI'] . " METHOD: " . $_SERVER['REQUEST_METHOD'], 3, __DIR__ . '/../error_log.log');
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? null;
//    error_log("\nAuth Header Present: " . ($authHeader ? 'Yes' : 'No'), 3, __DIR__ . '/../error_log.log');

    if (!$authHeader) {
        sendErrorResponse("Access denied. No token provided.");
    }

    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if (!$jwt) {
        sendErrorResponse("Access denied. Invalid token format.");
    }

    $secret_key = "ef74bf5bec8c73d9be269021af10dce5b4bbcf4921b98598b08359a77c19488c180ff000986791f3aed51c4bc1d60593516e8701066f5f41a90f6195c19f9a89";

    try {
        // Decode token
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

        if (!isset($decoded->data)) {
            sendErrorResponse("Access denied. Invalid token data.");
        }

        // Store user data globally
        $GLOBALS['current_user'] = (object)$decoded->data;
        $GLOBALS['jwt'] = $jwt;

    } catch (ExpiredException $e) {
        // Handle token expiration (attempt renewal)
        renewToken($jwt);
    } catch (Exception $e) {
        sendErrorResponse("Access denied. " . $e->getMessage());
    }
}

function checkAdminMiddleware(): void
{
    checkJwtMiddleware(); // Decode token first, store in $GLOBALS

    if (empty($GLOBALS['current_user']) || $GLOBALS['current_user']->role !== 'admin') {
        sendErrorResponse("Access denied. Admins only.", 403);
    }
}


function renewToken(string $expiredJwt): void
{
    try {
        list($header, $payload, $signature) = explode(".", $expiredJwt);
        $decoded = json_decode(base64_decode($payload));

        if (!isset($decoded->data->id) || !isset($decoded->data->refreshToken)) {
            sendErrorResponse("Access denied. Invalid token payload.");
        }

        $userId = $decoded->data->id;
        $refreshToken = $decoded->data->refreshToken;

        $userService = new UserService();
        $newJWT = $userService->refreshJWT($userId, $refreshToken);
        error_log("\nTOKEN REFRESHED" . "\n", 3, __DIR__ . '/../error_log.log');

        // Decode new JWT
        $secret_key = "ef74bf5bec8c73d9be269021af10dce5b4bbcf4921b98598b08359a77c19488c180ff000986791f3aed51c4bc1d60593516e8701066f5f41a90f6195c19f9a89";
        $newJWTdecoded = JWT::decode($newJWT, new Key($secret_key, 'HS256'));

        // Update global user data
        $GLOBALS['current_user'] = (object)$newJWTdecoded->data;
        $GLOBALS['jwt'] = $newJWT;

        // Send new token to the client
        header('Authorization: Bearer ' . $newJWT);
    } catch (Exception $e) {
        sendErrorResponse("\nToken renewal failed. " . $e->getMessage());
    }
}

function sendErrorResponse(string $message, int $statusCode = 401): void
{
    error_log("\nJWT Middleware Error: " . $message, 3, __DIR__ . '/../error_log.log');
    http_response_code($statusCode);
    echo json_encode(["message" => $message]);
    exit;
}
