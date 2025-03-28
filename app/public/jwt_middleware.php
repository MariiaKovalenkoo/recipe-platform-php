<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Services\UserService;

function checkJwtMiddleware(): void
{
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? null;

    if (!$authHeader) {
        sendErrorResponse("Access denied. No token provided.");
    }

    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if (!$jwt) {
        sendErrorResponse("Access denied. Invalid token format.");
    }

    $secret_key = "secret_key";

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
        $secret_key = "secret_key";
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
