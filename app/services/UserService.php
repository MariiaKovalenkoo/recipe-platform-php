<?php

namespace Services;

use Exception;
use Firebase\JWT\JWT;
use Models\User;
use Repositories\UserRepository;
use Services\exceptions\BadRequestException;
use Services\exceptions\UnauthorizedException;

class UserService
{

    private $repository;

    function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function authenticateUser($postedUser): User
    {
        $this->validateRequiredFields($postedUser, ['email', 'password']);

        $user = $this->repository->getUserByEmail($postedUser->getEmail());
        if (!$user)
            throw new UnauthorizedException("Invalid email.");

        $isPasswordValid = $this->verifyPassword($postedUser->getPassword(), $user->getPassword());
        if (!$isPasswordValid)
            throw new UnauthorizedException("Invalid password.");

        $user->setPassword("");

        return $user;
    }

    public function registerUser(User $postedUser): User
    {
        $this->validateRequiredFields($postedUser, ['email', 'password', 'firstName', 'lastName']);

        if ($this->repository->getUserByEmail($postedUser->getEmail())) {
            throw new BadRequestException("Email is already in use.");
        }

        if (strlen($postedUser->getPassword()) < 8) {
            throw new BadRequestException("Password must be at least 8 characters long.");
        }

        $hashedPassword = $this->hashPassword($postedUser->getPassword());
        $postedUser->setPassword($hashedPassword);
        $postedUser->setIsAdmin(false);

        $newUser = $this->repository->createUser($postedUser);
        if (!$newUser) {
            throw new Exception("An error occurred while registering the user.");
        }
        return $newUser;
    }

    private function validateRequiredFields(User $user, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            $getter = 'get' . ucfirst($field);
            if (!method_exists($user, $getter)) {
                throw new Exception("Invalid field: $field");
            }

            $value = $user->$getter();
            if (empty($value)) {
                throw new BadRequestException(ucfirst($field) . " is required.");
            }
        }
    }

    private function verifyPassword($input, $hash): bool
    {
        return password_verify($input, $hash);
    }

    private function hashPassword($password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    function generateJwt($user)
    {
        $refreshToken = bin2hex(random_bytes(32));
        $result = $this->repository->updateRefreshToken($user, $refreshToken);

        if (!$result) {
            throw new Exception("An error occurred while updating token in the database.");
        }

        $currentTime = time();
        $payload = array(
            "iss" => "localhost",
            "aud" => "localhost",
            "iat" => $currentTime,
            "nbf" => $currentTime,
            "exp" => $currentTime + 900,
            "data" => array(
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "role" => $user->getIsAdmin() ? "admin" : "user",
                "refreshToken" => $refreshToken
            )
        );
        return JWT::encode($payload, "ef74bf5bec8c73d9be269021af10dce5b4bbcf4921b98598b08359a77c19488c180ff000986791f3aed51c4bc1d60593516e8701066f5f41a90f6195c19f9a89", 'HS256');
    }

    function refreshJWT($userId, $refreshToken): string
    {
        try {
            $user = $this->repository->getUserById($userId);

            if ($refreshToken !== $user->getRefreshToken()) {
                error_log("Tokens don't match. Refresh Token is: " . $refreshToken . " Refresh token from db: " . $user->getRefreshToken() . "\n", 3, __DIR__ . '/../error_log.log');
                throw new UnauthorizedException("Invalid refresh token. Tokens don't match.");
            }
            return $this->generateJwt($user);
        } catch (Exception $e) {
            error_log("Refresh Token Error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new UnauthorizedException("An error occurred while refreshing the token. ");
        }
    }
}
