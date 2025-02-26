<?php

namespace Repositories;

use Exception;
use Models\User;
use PDO;
use PDOException;

class UserRepository extends Repository
{
    function getUserByEmail($email): ?User
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

            $stmt->bindParam(':email', $user->getEmail());
            $stmt->bindParam(':password', $user->getPassword());
            $stmt->bindParam(':firstName', $user->getFirstName());
            $stmt->bindParam(':lastName', $user->getLastName());
            $stmt->bindValue(':isAdmin', false, PDO::PARAM_BOOL); // Default to regular user

            $stmt->execute();
            // Get the ID of the newly inserted user
            $userId = $this->connection->lastInsertId();
            return $this->getUserById($userId);
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
            $stmt = $this->connection->prepare("SELECT * FROM User WHERE userId = :userId");
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
