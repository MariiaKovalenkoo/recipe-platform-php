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

            if (empty($postedUser->getEmail())) {
                $this->respondWithError(400, "Email is required.");
                return;
            }

            if (empty($postedUser->getPassword())) {
                $this->respondWithError(400, "Password is required.");
                return;
            }

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

            if (empty($postedUser->getEmail())) {
                $this->respondWithError(400, "Email is required.");
                return;
            }

            // Check if the user with this email already exists
            if ($this->service->getUserByEmail($postedUser->getEmail())) {
                $this->respondWithError(400, "Email is already registered.");
                return;
            }

//            if (empty($postedUser->getPassword()) || strlen($postedUser->getPassword()) < 8) {
//                $this->respondWithError(400, "Password must be at least 8 characters long.");
//                return;
//            }

            if (empty($postedUser->getFirstName()) || strlen($postedUser->getFirstName()) > 255) {
                $this->respondWithError(400, "First name is required.");
                return;
            }

            if (empty($postedUser->getLastName()) || strlen($postedUser->getLastName()) > 255) {
                $this->respondWithError(400, "Last name is required.");
                return;
            }

            $createdUser = $this->service->registerUser($postedUser);
            $tokenResponse = $this->generateJwt($createdUser);

            error_log("registered user: user registered", 3, __DIR__ . '/../error_log.log');


            $this->respondOk($tokenResponse);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }
}
