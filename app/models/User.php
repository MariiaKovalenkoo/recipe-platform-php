<?php
namespace Models;

class User {

    private int $id;
    private string $username;
    private string $password;
    private string $firstName;
    private string $lastName;
    private string $email;

    private bool $isAdmin;

    public function getId(): int {
        return $this->id;
    }

    public function getFullName(): string {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function setUsername(string $username): void {
        $this->username = $username;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function getFirstName(): string {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void {
        $this->firstName = $firstName;
    }

    public function getLastName(): string {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void {
        $this->lastName = $lastName;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getIsAdmin(): bool {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): void {
        $this->isAdmin = $isAdmin;
    }
}