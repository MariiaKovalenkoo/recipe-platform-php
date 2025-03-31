<?php

namespace Controllers;

use Exception;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class Controller
{
    function addTokenToResponse($data)
    {
        # if the JWT is not set, return the data as is
        if (!isset($GLOBALS['jwt'])) {
            return $data;
        }

        if (is_array($data)) {
            $data['token'] = $GLOBALS['jwt'];
            return $data;
        }

        if (is_object($data)) {
            $arr = json_decode(json_encode($data), true);
            $arr['token'] = $GLOBALS['jwt'];
            return $arr;
        }

        return ['data' => $data, 'token' => $GLOBALS['jwt']];
    }

    function respondOk($data)
    {
        $this->respondWithCode(200, $data);
    }

    function respondCreated($data)
    {
        $this->respondWithCode(201, $data);
    }

    function respondWithError($httpcode, $message)
    {
        $data = array('message' => $message);
        $data = $this->addTokenToResponse($data);
        $this->respondWithCode($httpcode, $data);
    }

    private function respondWithCode($httpcode, $data)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpcode);
        $data = $this->addTokenToResponse($data);
        echo json_encode($data);
    }

    protected function getJsonData()
    {
        return json_decode(file_get_contents("php://input"), true);
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

    protected function getPostedFormAndFiles(): array
    {
        return [
            'form' => $_POST,
            'files' => $_FILES,
        ];
    }

    protected function getCurrentUserId()
    {
        if (!isset($GLOBALS['current_user'])) {
            $this->respondWithError(401, "Unauthorized: Please log in.");
        }
        return $GLOBALS['current_user']->id;
    }

    protected function getCurrentUserRole()
    {
        if (!isset($GLOBALS['current_user'])) {
            $this->respondWithError(401, "Unauthorized: Please log in.");
        }
        return $GLOBALS['current_user']->role;
    }
}
