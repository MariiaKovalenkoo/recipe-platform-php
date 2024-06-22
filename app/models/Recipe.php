<?php

namespace Models;

use Models\enums\ApprovalStatus;
use Models\enums\CuisineType;
use Models\enums\DietaryPreference;
use Models\enums\MealType;

class Recipe implements \JsonSerializable{
    private int $id ;
    private int $userId;
    private string $name;
    private bool $isPublic;
    private MealType $mealType;
    private DietaryPreference $dietaryPreference;
    private CuisineType $cuisineType;
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

    public function getName(): string {
        return $this->name;
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

    public function getMealType(): MealType {
        return $this->mealType;
    }

    public function getDietaryPreference(): DietaryPreference {
        return $this->dietaryPreference;
    }

    public function getCuisineType(): CuisineType {
        return $this->cuisineType;
    }
    public function setIsPublic(bool $isPublic): void {
        $this->isPublic = $isPublic;
    }

    public function setMealType(string $mealType): void {
        $this->mealType = MealType::from($mealType);
    }

    public function setDietaryPreference(string $preference): void {
        $this->dietaryPreference = DietaryPreference::from($preference);
    }

    public function setCuisineType(string $cuisine): void {
        $this->cuisineType = CuisineType::from($cuisine);
    }

    public function setName(string $name): void
    {
        $this->name = $name;
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

    public function setStatus(string $status): void {
        $this->status = ApprovalStatus::from($status);
    }
    private function encodeImageToBase64($imagePath): string
    {
        $imagePath =  __DIR__ . '/../public/' . $imagePath;
        $imageData = file_get_contents($imagePath);
        return base64_encode($imageData);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'name' => $this->name,
            'isPublic' => $this->isPublic,
            'mealType' => $this->mealType,
            'dietaryPreference' => $this->dietaryPreference,
            'cuisineType' => $this->cuisineType,
            'description' => $this->description,
            'ingredients' => $this->ingredients,
            'instructions' => $this->instructions,
            "image" => 'data:image/jpeg;base64,' . $this->encodeImageToBase64($this->imgPath),
            'status' => $this->status
        ];
        // return get_object_vars($this);
    }
}