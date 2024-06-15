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

        try{
        $postedUser = $this->createObjectFromPostedJson("Models\\User");

        $user = $this->service->checkUsernamePassword($postedUser->getUsername(), $postedUser->getPassword());

        if (!$user) {
            $this->respondWithError(401, "Invalid username or password");
            return;
        }

        $tokenResponse = $this->generateJwt($user);

        $this->respond($tokenResponse);}
        catch(\Exception $e){
            $this->respondWithError(500, $e->getMessage());
        }
    }
}
