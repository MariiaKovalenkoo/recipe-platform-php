<?php

namespace Controllers;

use Services\UserService;
use Exception;

class UserController extends Controller
{
    private UserService $service;

    function __construct()
    {
        $this->service = new UserService();
    }

    // change this catch block
    public function login(): void
    {
        try {
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
            $user = $this->service->authenticateUser($postedUser);

            $tokenResponse = $this->generateJwt($user);
            $this->respondOk($tokenResponse);}
        catch(Exception $e){
            if (str_contains($e->getMessage(), "Invalid email or password")) {
                $this->respondWithError(401, "Invalid email or password");
            } else {
                $this->respondWithError(500, "An unexpected error occurred.");
            }
        }
    }

    // check this
    public function register(): void
    {
        try {
            $postedUser = $this->createObjectFromPostedJson("Models\\User");

            // Check if the user with this email already exists
            if ($this->service->getUserByEmail($postedUser->getEmail())) {
                $this->respondWithError(400, "Email is already registered.");
                return;
            }

            $createdUser = $this->service->registerUser($postedUser);
            $tokenResponse = $this->generateJwt($createdUser);

            $this->respondOk($tokenResponse);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }
}
