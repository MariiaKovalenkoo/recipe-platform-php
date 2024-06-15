<?php

namespace Models;

use Models\enums\ApprovalStatus;

class Recipe {
    private int $id ;
    private int $userId;
    private string $recipeName;
    private bool $isPublic;
    private string $mealType;
    private string $dietaryPreference;
    private string $cuisineType;
    private string $description;
    private string $ingredients;
    private string $instructions;
    private string $imgPath;

    private ApprovalStatus $status;

    public function getImgPath(): string {
        return $this->imgPath;
    }

    public function setImgPath(string $imgPath): void {
        $this->imgPath = $imgPath;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function getRecipeName(): string {
        return $this->recipeName;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getIngredients(): string {
        return $this->ingredients;
    }

    public function getInstructions(): string {
        return $this->instructions;
    }

    public function getIsPublic(): bool {
        return $this->isPublic;
    }

    public function getMealType(): string {
        return $this->mealType;
    }

    public function getDietaryPreference(): string {
        return $this->dietaryPreference;
    }

    public function getCuisineType(): string {
        return $this->cuisineType;
    }
    public function setIsPublic(bool $isPublic): void {
        $this->isPublic = $isPublic;
    }

    public function setMealType(string $mealType): void {
        $this->mealType = $mealType;
    }

    public function setDietaryPreference(string $preference): void {
        $this->dietaryPreference = $preference;
    }

    public function setCuisineType(string $cuisine): void {
        $this->cuisineType = $cuisine;
    }

    public function setRecipeName(string $recipeName): void
    {
        $this->recipeName = $recipeName;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setIngredients(string $ingredients): void
    {
        $this->ingredients = $ingredients;
    }

    public function setInstructions(string $instructions): void
    {
        $this->instructions = $instructions;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getStatus(): ApprovalStatus {
        return $this->status;
    }

    public function setStatus(ApprovalStatus $status): void {
        $this->status = $status;
    }
}