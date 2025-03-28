<?php

namespace Models;

use DateTime;

class FavoriteRecipe implements \JsonSerializable
{
    private int $id;
    private int $userId;
    private int $recipeId;
    private Recipe $recipe;
    private DateTime $addedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getRecipeId(): int
    {
        return $this->recipeId;
    }

    public function setRecipeId(int $recipeId): void
    {
        $this->recipeId = $recipeId;
    }

    public function getRecipe(): Recipe
    {
        return $this->recipe;
    }

    public function getAddedAt(): DateTime
    {
        return $this->addedAt;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function setRecipe(Recipe $recipe): void
    {
        $this->recipe = $recipe;
    }

    public function setAddedAt(DateTime|string $addedAt): void
    {
        if (is_string($addedAt)) {
            $addedAt = new DateTime($addedAt);
        }
        $this->addedAt = $addedAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'favoriteId' => $this->id,
            'id' => $this->recipeId,
            'userId' => $this->userId,
            'name' => $this->recipe->getName(),
            'mealType' => $this->recipe->getMealType(),
            'dietaryPreference' => $this->recipe->getDietaryPreference(),
            'cuisineType' => $this->recipe->getCuisineType(),
            'description' => $this->recipe->getDescription(),
            'ingredients' => $this->recipe->getIngredients(),
            'instructions' => $this->recipe->getInstructions(),
            "image" => 'data:image/jpeg;base64,' . $this->recipe->encodeImageToBase64($this->recipe->getImgPath()),
            'status' => $this->recipe->getStatus(),
        ];
    }
}