<?php

namespace Controllers;

use Exception;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class Controller
{

    function respondOk($data)
    {
        $this->respondWithCode(200, $data);
    }

    function respondWithError($httpcode, $message)
    {
        $data = array('message' => $message);
        $this->respondWithCode($httpcode, $data);
    }

    private function respondWithCode($httpcode, $data)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpcode);
        echo json_encode($data);
    }

    function createObjectFromPostedJson($className)
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json);

        $object = new $className();
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                continue;
            }
            $methodName = 'set' . ucfirst($key);
            if (method_exists($object, $methodName)) {
                $object->$methodName($value);
            }
        }
        return $object;
    }

    function generateJwt($user)
    {
        $currentTime = time();
        $payload = array(
            "iss" => "localhost",
            "aud" => "localhost",
            "iat" => $currentTime,
            "nbf" => $currentTime, // Or $currentTime + a shorter interval if necessary
            "exp" => $currentTime + 3600, // Reducing to 1 hour for better security
            "data" => array(
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "role" => $user->getIsAdmin() ? "admin" : "user",
            )
        );
        $jwt = JWT::encode($payload, "secret_key", 'HS256');
        // change key
        return array(
            "message" => 'Successful login',
            "token" => $jwt,
            "expiresAt" => $payload['exp']
        );
    }
}
