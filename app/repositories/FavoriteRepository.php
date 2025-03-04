<?php

namespace Repositories;

use Exception;
use Models\Recipe;
use Models\FavoriteRecipe;

class FavoriteRepository extends Repository
{
    public function addFavorite(int $userId, int $recipeId): bool
    {
        try {
            $stmt = $this->connection->prepare("INSERT INTO UserFavorite (userId, recipeId) VALUES (:userId, :recipeId)");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':recipeId', $recipeId, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function removeFavorite(int $userId, int $recipeId): bool
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM UserFavorite WHERE userId = :userId AND recipeId = :recipeId");

            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':recipeId', $recipeId, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function isFavorite(int $userId, int $recipeId): bool
    {
        try {
            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM UserFavorite WHERE userId = :userId AND recipeId = :recipeId");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':recipeId', $recipeId, PDO::PARAM_INT);
            $stmt->execute();

            return (bool)$stmt->fetchColumn();

        } catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getFavoritesByUser($userId): array
    {
        try {
            $stmt = $this->connection->prepare("SELECT uf.*, r.recipeId, recipeName, description, ingredients, 
                                                    instructions, mealType, dietaryPreference, cuisineType, imgPath
                                                    FROM UserFavorite uf JOIN Recipe r ON uf.recipeId = r.recipeId WHERE uf.userId = :userId");

            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $favData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $favorites = [];
            foreach ($favData as $data) {
                $favorites[] = $this->rowToFavoriteRecipe($data);
            }
            return $favorites;
        }
        catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Helper function to convert a database row to a FavoriteRecipe object
    private function rowToFavoriteRecipe(array $data): FavoriteRecipe
    {
        // Create Recipe object and set properties
        $recipe = new Recipe();
        $recipe->setId($data['recipeId']);
        $recipe->setName($data['recipeName']);
        $recipe->setDescription($data['description']);
        $recipe->setIngredients($data['ingredients']);
        $recipe->setInstructions($data['instructions']);
        $recipe->setMealType($data['mealType']);
        $recipe->setDietaryPreference($data['dietaryPreference']);
        $recipe->setCuisineType($data['cuisineType']);
        $recipe->setImgPath($data['imgPath']);

        // Create UserFavorite object and set recipe property
        $favRecipe = new FavoriteRecipe();
        $favRecipe->setUserId($data['userId']);
        $favRecipe->setId($data['favoriteId']);
        $favRecipe->setAddedAt($data['addedAt']);
        $favRecipe->setRecipe($recipe);

        return $favRecipe;
    }
}