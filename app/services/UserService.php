<?php
namespace Services;

use Exception;
use Firebase\JWT\JWT;
use Models\User;
use Repositories\UserRepository;
use Services\exceptions\BadRequestException;
use Services\exceptions\UnauthorizedException;

class UserService {

    private $repository;

    function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function authenticateUser($postedUser): User
    {
        if (empty($postedUser->getEmail())) {
            throw new BadRequestException("Email is required.");
        }

        if (empty($postedUser->getPassword())) {
            throw new BadRequestException("Password is required.");
        }

        $user = $this->repository->getUserByEmail($postedUser->getEmail());
        if (!$user)
            throw new UnauthorizedException("Invalid email.");

        $isPasswordValid = $this->verifyPassword($postedUser->getPassword(), $user->getPassword());
        if (!$isPasswordValid)
            throw new UnauthorizedException("Invalid password.");

        $user->setPassword("");

        return $user;
    }

    public function registerUser(User $postedUser): ?User
    {
        if (empty($postedUser->getEmail())) {
            throw new BadRequestException("Email is required.");
        }

        if (empty($postedUser->getPassword())) {
            throw new BadRequestException("Password is required.");
        }

        if ($this->repository->getUserByEmail($postedUser->getEmail())) {
            throw new BadRequestException("Email is already in use.");
        }

        if (strlen($postedUser->getPassword()) < 8) {
            throw new BadRequestException("Password must be at least 8 characters long.");
        }

        if (empty($postedUser->getFirstName())) {
            throw new BadRequestException("First name is required.");
        }

        if (empty($postedUser->getLastName())) {
            throw new BadRequestException("Last name is required.");
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
            return false;
        }

        $currentTime = time();
        $payload = array(
            "iss" => "localhost",
            "aud" => "localhost",
            "iat" => $currentTime,
            "nbf" => $currentTime, // Or $currentTime + a shorter interval if necessary
            "exp" => $currentTime + 10, // Reducing to 1 hour for better security
            "data" => array(
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "role" => $user->getIsAdmin() ? "admin" : "user",
                "refreshToken" => $refreshToken
            )
        );
        return JWT::encode($payload, "secret_key", 'HS256');
    }

    function refreshJWT($userId, $refreshToken): string
    {
        try {
            $user = $this->repository->getUserById($userId);

            if ($refreshToken !== $user->getRefreshToken()) {
                error_log("Refresh Token Error: Invalid refresh token", 3, __DIR__ . '/../error_log.log');
                http_response_code(401);
                echo json_encode(array("message" => "Access denied. Invalid refresh token."));
                exit;
            }

            return $this->generateJwt($user);
        } catch (Exception $e) {
            error_log("Refresh Token Error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            http_response_code(401);
            echo json_encode(array("message" => "Access denied. " . $e->getMessage()));
            exit;
        }
    }
}
