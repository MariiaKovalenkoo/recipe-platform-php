<?php

namespace Controllers;

use Services\UserService;


class UserController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new UserService();
    }

    public function login()
    {

        $postedUser = $this->createObjectFromPostedJson("Models\\User");

        $user = $this->service->checkUsernamePassword($postedUser->username, $postedUser->password);

        if (!$user) {
            $this->respondWithError(401, "Invalid username or password");
            return;
        }

        $tokenResponse = $this->generateJwt($user);

        $this->respond($tokenResponse);
    }
}
