<?php
namespace Services;

use Exception;
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
}
