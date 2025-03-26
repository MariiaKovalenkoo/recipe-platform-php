<?php
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Services\UserService;

function checkJwtMiddleware(): void
{
    $headers = getallheaders();
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

        //$GLOBALS['current_user'] = $decoded->data;
        $GLOBALS['current_user'] = (object) $decoded->data;
        $GLOBALS['jwt'] = $jwt;

        // Log decoded token and current user data
        //error_log("Decoded Token: " . print_r($decoded, true), 3, __DIR__ . '/../error_log.log');
        //error_log("Current User: " . print_r($GLOBALS['current_user'], true), 3, __DIR__ . '/../error_log.log');
        //error_log("JWT Middleware: User ID set to " . $GLOBALS['current_user']->id, 3, __DIR__ . '/../error_log.log');

    }
    catch (ExpiredException $e) {
        # renew token
        list($header, $payload, $signature) = explode(".", $jwt);
        $decoded = json_decode(base64_decode($payload));
        $userId = $decoded->data->id;

        $userService = new UserService();
        $newJWT = $userService->refreshJWT($userId, $decoded->data->refreshToken);

        if (!$newJWT) {
            error_log("JWT Middleware Error: Failed to refresh token", 3, __DIR__ . '/../error_log.log');
            http_response_code(401);
            echo json_encode(array("message" => "Access denied. Failed to refresh token."));
            exit;
        }

        $newJWTdecoded = JWT::decode($newJWT, new Key($secret_key, 'HS256'));
        $GLOBALS['current_user'] = (object) $newJWTdecoded->data;
        $GLOBALS['jwt'] = $newJWT;
    } catch (Exception $e) {
        error_log("JWT Middleware Error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. " . $e->getMessage()));
        exit;
    }
}

