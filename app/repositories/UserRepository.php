<?php

namespace Repositories;

use PDO;
use PDOException;
use Repositories\Repository;

class UserRepository extends Repository
{
    function checkEmailPassword($email, $password)
    {
        try {
            // retrieve the user with the given username
            $stmt = $this->connection->prepare("SELECT id, password, email, isAdmin FROM User WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\User');
            $user = $stmt->fetch();

            return $user;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    // hash the password (currently uses bcrypt)
    function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getUserByEmail($email)
    {
        $stmt = $this->connection->prepare("SELECT id, email FROM User WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($user)
    {
        $stmt = $this->connection->prepare("
            INSERT INTO User (email, password, firstName, lastName, isAdmin) 
            VALUES (:email, :password, :firstName, :lastName, :isAdmin)
        ");

        $stmt->bindParam(':email', $user->getEmail());
        $stmt->bindParam(':password', $user->getPassword());
        $stmt->bindParam(':firstName', $user->getFirstName());
        $stmt->bindParam(':lastName', $user->getLastName());
        $stmt->bindValue(':isAdmin', false, PDO::PARAM_BOOL); // Default to regular user

        $stmt->execute();
        // Get the ID of the newly inserted user
        $userId = $this->connection->lastInsertId();
        $createdUser = $this->getUserById($userId);
        return $createdUser;
    }

//    function checkUsernameExists($enteredUsername): bool {
//        $stmt = $this->connection->prepare("SELECT COUNT(*) as user_count FROM User WHERE username = ?");
//        $stmt->execute([$enteredUsername]);
//        $result = $stmt->fetch(PDO::FETCH_ASSOC);
//        return ($result['user_count'] > 0);
//    }
//
//    public function getHashedPasswordByUsername($username): ?string
//    {
//        $stmt = $this->connection->prepare("SELECT password FROM User WHERE username = ?");
//        $stmt->execute([$username]);
//        $hashedPassword = $stmt->fetchColumn();
//        return ($hashedPassword !== false) ? $hashedPassword : null;
//    }
//
//    public function getUserById($userId): ?User
//    {
//        $stmt = $this->connection->prepare("SELECT * FROM User WHERE userId = ?");
//        $stmt->setFetchMode(PDO::FETCH_CLASS, 'App\Models\User');
//        $stmt->execute([$userId]);
//        $user = $stmt->fetch(PDO::FETCH_CLASS);
//        return ($user !== false) ? $user : null;
//    }
//
//    public function getUserByUsername($username): ?User
//    {
//        $stmt = $this->connection->prepare("SELECT * FROM User WHERE username = ?");
//        $stmt->setFetchMode(PDO::FETCH_CLASS, 'App\Models\User');
//        $stmt->execute([$username]);
//        $user = $stmt->fetch(PDO::FETCH_CLASS);
//        return ($user !== false) ? $user : null;
//    }
//
//    public function checkEmailExists($enteredEmail) : bool
//    {
//        $stmt = $this->connection->prepare("SELECT COUNT(*) as user_count FROM User WHERE email = ?");
//        $stmt->execute([$enteredEmail]);
//        $result = $stmt->fetch(PDO::FETCH_ASSOC);
//        return ($result['user_count'] > 0);
//    }
//
//    public function createNewUser(User $user): ?User
//    {
//        $stmt = $this->connection->prepare("INSERT INTO User (username, password, firstName, lastName, email) VALUES (?, ?, ?, ?, ?)");
//
//        $username = $user->getUsername();
//        $password = $user->getPassword();
//        $firstName = $user->getFirstName();
//        $lastName = $user->getLastName();
//        $email = $user->getEmail();
//        $stmt->execute([$username, $password, $firstName, $lastName, $email]);
//
//        if ($stmt->rowCount() > 0) {
//            $lastInsertId = $this->connection->lastInsertId();
//            return $this->getUserById($lastInsertId);
//        }
//        return null;
//    }
}
