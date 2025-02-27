<?php

namespace Repositories;

use Exception;
use Models\User;
use PDO;
use PDOException;

class UserRepository extends Repository
{
    function getUserByEmail($email): User|false
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM User WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\User');
            return $stmt->fetch();

        }
        catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        }
    }

    public function createUser($user): ?User
    {
        try {

            $stmt = $this->connection->prepare("INSERT INTO User (email, password, firstName, lastName, isAdmin) 
                                                        VALUES (:email, :password, :firstName, :lastName, :isAdmin)");

            $email = $user->getEmail();
            $password = $user->getPassword();
            $firstName = $user->getFirstName();
            $lastName = $user->getLastName();
            $isAdmin = $user->getIsAdmin();

            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':firstName', $firstName);
            $stmt->bindParam(':lastName', $lastName);
            $stmt->bindValue(':isAdmin', $isAdmin, PDO::PARAM_BOOL);

            $stmt->execute();
            $user->setId((int)$this->connection->lastInsertId());
            return $user;

        }
        catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getUserById($userId): ?User
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM User WHERE id = :userId");
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\User');
            return $stmt->fetch();
        }
        catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        }
    }
}
