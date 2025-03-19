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

        $data['token'] = $GLOBALS['jwt'];
        return $data;
    }

    function respondOk($data)
    {
        $data = $this->addTokenToResponse($data);
        $this->respondWithCode(200, $data);
    }

    function respondCreated($data)
    {
        $data = $this->addTokenToResponse($data);
        $this->respondWithCode(201, $data);
    }

    function respondAccepted($data)
    {
        $data = $this->addTokenToResponse($data);
        $this->respondWithCode(202, $data);
    }

    function respondNoContent()
    {
        $this->respondWithCode(204, null);
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

    function createObjectFromPostedJson($className)
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $data = $this->addTokenToResponse($data);

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
}
