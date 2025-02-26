<?php
namespace Services;

use Models\User;
use Repositories\UserRepository;
use Exception;

class UserService {

    private $repository;

    function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function authenticateUser($postedUser)
    {
        try {
            $user = $this->repository->getUserByEmail($postedUser->getEmail());
        } catch (Exception $e) {
            throw new Exception("Invalid email or password");
        }

        // verify if the password matches the hash in the database
        $result = $this->verifyPassword($postedUser->getPassword(), $user->getPassword());

        if (!$result)
            throw new Exception("Invalid email or password");

        $user->setPassword("");

        return $user;
    }

    // verify the password hash
    private function verifyPassword($input, $hash): bool
    {
        return password_verify($input, $hash);
    }

    public function registerUser(User $postedUser): ?User
    {
        $hashedPassword = password_hash($postedUser->getPassword(), PASSWORD_DEFAULT);
        $postedUser->setPassword($hashedPassword);

        return $this->repository->createUser($postedUser);
    }

    public function getUserByEmail($email)
    {
        return $this->repository->getUserByEmail($email);
    }
}
