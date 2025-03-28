<?php

namespace Controllers;

use Services\exceptions\BadRequestException;
use Services\exceptions\UnauthorizedException;
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

            $user = $this->service->authenticateUser($postedUser);

            $token = $this->service->generateJwt($user);

            $tokenResponse = array(
                "message" => 'Successful login',
                "token" => $token
            );
            $this->respondOk($tokenResponse);
        } catch (BadRequestException $e) {
            $this->respondWithError(400, $e->getMessage());
        } catch (UnauthorizedException $e) {
            $this->respondWithError(401, $e->getMessage());
        } catch (Exception $e) {
            $this->respondWithError(500, $e);
            error_log("Login error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
        }
    }

    public function register(): void
    {
        try {
            $postedUser = $this->createObjectFromPostedJson("Models\\User");

            $createdUser = $this->service->registerUser($postedUser);
            $token = $this->service->generateJwt($createdUser);

            $tokenResponse = array(
                "message" => 'User registered successfully',
                "token" => $token
            );

            $this->respondOk($tokenResponse);
        } catch (BadRequestException $e) {
            $this->respondWithError(400, $e->getMessage());
        } catch (Exception $e) {
            $this->respondWithError(500, "An unexpected error occurred.");
            error_log("Register error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
        }
    }
}
