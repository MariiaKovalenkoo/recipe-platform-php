<?php

namespace Repositories;

use Models\Recipe;
use Models\FavoriteRecipe;

class FavoriteRepository extends Repository
{
    public function addToFavorite($userId, $recipeId): bool
    {
        $stmt = $this->connection->prepare("INSERT INTO UserFavorite (userId, recipeId) VALUES (?, ?)");
        $stmt->execute([$userId, $recipeId]);
        return $stmt->rowCount() > 0;
    }

    public function removeFromFavorite($userId, $recipeId): bool
    {
        $stmt = $this->connection->prepare("DELETE FROM UserFavorite WHERE userId = ? AND recipeId = ?");
        $stmt->execute([$userId, $recipeId]);
        return $stmt->rowCount() > 0;
    }

    public function isFavorite($userId, $recipeId): bool
    {
        $stmt = $this->connection->prepare("SELECT * FROM UserFavorite WHERE userId = ? AND recipeId = ?");
        $stmt->execute([$userId, $recipeId]);
        return $stmt->rowCount() > 0;
    }

    public function getFavoritesByUser($userId): array
    {
        $stmt = $this->connection->prepare("
        SELECT uf.*, r.recipeId, recipeName, description, ingredients, 
                                                instructions, mealType, dietaryPreference, 
                                                cuisineType, isPublic , imgPath
        FROM UserFavorite uf
        JOIN Recipe r ON uf.recipeId = r.recipeId
        WHERE uf.userId = ?
    ");
        $stmt->execute([$userId]);

        $favData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $favorites = [];
        foreach ($favData as $data) {
            $recipe = new Recipe();
            $recipe->setId($data['recipeId']);
            $recipe->setName($data['recipeName']);
            $recipe->setDescription($data['description']);
            $recipe->setIngredients($data['ingredients']);
            $recipe->setInstructions($data['instructions']);
            $recipe->setMealType($data['mealType']);
            $recipe->setDietaryPreference($data['dietaryPreference']);
            $recipe->setCuisineType($data['cuisineType']);
            $recipe->setIsPublic($data['isPublic']);
            $recipe->setImgPath($data['imgPath']);

            // Create UserFavorite object and set recipe property
            $favRecipe = new FavoriteRecipe();
            $favRecipe->setUserId($data['userId']);
            $favRecipe->setId($data['favoriteId']);
            $favRecipe->setAddedAt($data['addedAt']);
            $favRecipe->setRecipe($recipe);

            // Add to favorites array
            $favorites[] = $favRecipe;
        }
        return $favorites;
    }


}