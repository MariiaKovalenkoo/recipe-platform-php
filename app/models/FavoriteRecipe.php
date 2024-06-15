<?php

namespace Models;


class FavoriteRecipe
{
    private int $id;
    private int $userId;
    private int $recipeId;
    private Recipe $recipe;
    private \DateTime $addedAt;

    public function getId(): int
    {
        return $this->id;
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

    public function getAddedAt(): \DateTime
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

    public function setAddedAt(\DateTime $addedAt): void
    {
        $this->addedAt = $addedAt;
    }
}