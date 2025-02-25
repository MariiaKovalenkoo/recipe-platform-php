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

    public function login(): void
    {
        try {
            $postedUser = $this->createObjectFromPostedJson("Models\\User");

            $user = $this->service->checkEmailPassword($postedUser->getEmail(), $postedUser->getPassword());

            if (!$user) {
                $this->respondWithError(401, "Invalid email or password");
                return;
            }

            $tokenResponse = $this->generateJwt($user);

            $this->respondOk($tokenResponse);}
        catch(Exception $e){
            $this->respondWithError(500, $e->getMessage());
        }
    }
    public function register()
    {
        try {
            $postedUser = $this->createObjectFromPostedJson("Models\\User");

            // Check if email already exists (using a dedicated method for clarity)
            if ($this->service->getUserByEmail($postedUser->getEmail())) {
                $this->respondWithError(400, "Email is already registered.");
                return;
            }

            // Hash the password
            $hashedPassword = password_hash($postedUser->getPassword(), PASSWORD_DEFAULT);
            $postedUser->setPassword($hashedPassword);

            // Save user
            $createdUser = $this->service->createUser($postedUser);

            // Generate JWT and respond - structured like login
            $tokenResponse = $this->generateJwt($createdUser);

            $this->respondOk($tokenResponse);  // respond with token
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }
}
