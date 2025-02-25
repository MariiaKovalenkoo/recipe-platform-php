<?php
namespace Services;

use Repositories\UserRepository;

class UserService {

    private $repository;

    function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function checkEmailPassword($email, $password) {
        $user = $this->repository->checkEmailPassword($email, $password);

        if(!$user)
            return false;

        // verify if the password matches the hash in the database
        $result = $this->verifyPassword($password, $user->getPassword());

        if (!$result)
            return false;

        // do not pass the password hash to the caller
        $user->setPassword("");

        return $user;
    }

    public function getUserByEmail($email)
    {
        return $this->repository->getUserByEmail($email);
    }

    // verify the password hash
    private function verifyPassword($input, $hash): bool
    {
        return password_verify($input, $hash);
    }

    public function createUser($user)
    {
        return $this->repository->createUser($user);
    }

    // function checkUsernameExists($enteredUsername): bool {
    //     return $this->repository->checkUsernameExists($enteredUsername);
    // }

    // public function getHashedPasswordByUsername($username): string
    // {
    //     return $this->repository->getHashedPasswordByUsername($username);
    // }

//    public function getUserById($userId): User
//    {
//        return $this->repository->getUserById($userId);
//    }
//
//    public function getUserByUsername($username): User
//    {
//        return $this->repository->getUserByUsername($username);
//    }
//
//    public function checkEmailExists($email) : bool
//    {
//        return $this->repository->checkEmailExists($email);
//    }
//
//    public function createNewUser(User $user) : ?User
//    {
//        return $this->repository->createNewUser($user);
//    }
}
