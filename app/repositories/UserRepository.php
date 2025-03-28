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
            $user = $stmt->fetch();

            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
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

            if (!$stmt->execute()) {
                return null;
            }
            $user->setId((int)$this->connection->lastInsertId());
            return $user;

        } catch (PDOException $e) {
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
            $user = $stmt->fetch();

            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateRefreshToken($user, $refreshToken): bool
    {
        try {
            $stmt = $this->connection->prepare("UPDATE User SET refreshToken = :refreshToken WHERE id = :userId");
            $userId = $user->getId();
            $stmt->bindParam(':refreshToken', $refreshToken);
            $stmt->bindParam(':userId', $userId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }
}
