<?php

namespace Repositories;

use Exception;
use Models\Recipe;
use Models\FavoriteRecipe;
use PDO;
use PDOException;

class FavoriteRepository extends Repository
{
    public function addFavorite(int $userId, int $recipeId): bool
    {
        try {
            $stmt = $this->connection->prepare("INSERT INTO FavoriteRecipe (userId, recipeId) VALUES (:userId, :recipeId)");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':recipeId', $recipeId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function removeFavorite(int $userId, int $recipeId): bool
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM FavoriteRecipe WHERE userId = :userId AND recipeId = :recipeId");

            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':recipeId', $recipeId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function isFavorite(int $userId, int $recipeId): bool
    {
        try {
            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM FavoriteRecipe WHERE userId = :userId AND recipeId = :recipeId");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':recipeId', $recipeId, PDO::PARAM_INT);
            $stmt->execute();

            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getUserFavorites(int $userId, int $offset, int $limit): array
    {
        try {
            $stmt = $this->connection->prepare("
            SELECT SQL_CALC_FOUND_ROWS 
                uf.*,
                r.id AS recipeId, 
                r.name, 
                r.description, 
                r.ingredients, 
                r.instructions, 
                r.mealType, 
                r.dietaryPreference, 
                r.cuisineType, 
                r.imgPath, 
                r.status
            FROM FavoriteRecipe uf
            JOIN Recipe r ON uf.recipeId = r.id
            WHERE uf.userId = :userId
            ORDER BY uf.addedAt DESC
            LIMIT :limit OFFSET :offset
        ");

            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $favData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalStmt = $this->connection->query("SELECT FOUND_ROWS()");
            $total = (int)$totalStmt->fetchColumn();

            $favorites = [];
            foreach ($favData as $data) {
                $favorites[] = $this->rowToFavoriteRecipe($data);
            }

            return [
                'recipes' => $favorites,
                'total' => $total
            ];
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while fetching favorites.");
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
        $recipe->setName($data['name']);
        $recipe->setUserId($data['userId']);
        $recipe->setDescription($data['description']);
        $recipe->setIngredients($data['ingredients']);
        $recipe->setInstructions($data['instructions']);
        $recipe->setMealType($data['mealType']);
        $recipe->setDietaryPreference($data['dietaryPreference']);
        $recipe->setCuisineType($data['cuisineType']);
        $recipe->setStatus($data['status']);
        $recipe->setImgPath($data['imgPath']);

        // Create UserFavorite object and set recipe property
        $favRecipe = new FavoriteRecipe();
        $favRecipe->setId($data['id']);
        $favRecipe->setUserId($data['userId']);
        $favRecipe->setAddedAt($data['addedAt']);
        $favRecipe->setRecipeId($data['recipeId']);
        $favRecipe->setRecipe($recipe);

        return $favRecipe;
    }
}