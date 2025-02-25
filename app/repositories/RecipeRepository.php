<?php

namespace Repositories;

use Exception;
use Models\enums\ApprovalStatus;
use Models\Recipe;
use PDO;
use PDOException;

class RecipeRepository extends Repository
{
    // get all/any recipes (for admin)
    public function getAllRecipes(int $offset = 0, int $limit = 10, ?string $status = null, ?string $mealType = null, ?string $cuisineType = null, ?string $dietaryPreference = null): array {
        return $this->getRecipes(null, $status, $mealType, $cuisineType, $dietaryPreference,  $offset, $limit);
    }

    // get public recipes (approved only)
    public function getPublicRecipes(int $offset = 0, int $limit = 10, string $status = null, ?string $mealType = null, ?string $cuisineType = null, ?string $dietaryPreference = null): array
    {
        return $this->getRecipes(null, $status, $mealType, $cuisineType, $dietaryPreference, $offset, $limit);
    }

    // get user recipes (any status)
    public function getUserRecipes(int $userId, int $offset = 0, int $limit = 10, ?string $status = null, ?string $mealType = null, ?string $cuisineType = null, ?string $dietaryPreference = null): array
    {
        return $this->getRecipes($userId, $status, $mealType, $cuisineType, $dietaryPreference, $offset, $limit);
    }

    // get recipe by id
    public function getRecipeById(int $id): ?Recipe
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM Recipe WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->rowToRecipe($row);
        }
        catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());        }
    }

    // helper function to convert a row from the database to a Recipe object
    private function rowToRecipe(mixed $row): Recipe
    {
        $recipe = new Recipe();
        $recipe->setId($row['id']);
        $recipe->setName($row['name']);
        $recipe->setIngredients($row['ingredients']);
        $recipe->setInstructions($row['instructions']);
        $recipe->setImgPath($row['imgPath']);
        $recipe->setMealType($row['mealType']);
        $recipe->setDietaryPreference($row['dietaryPreference']);
        $recipe->setCuisineType($row['cuisineType']);
        $recipe->setDescription($row['description']);
        $recipe->setStatus($row['status']);
        $recipe->setUserId($row['userId']);
        return $recipe;
    }

    // helper function to get recipes based on parameters such as userId, status, mealType, cuisineType, dietaryPreference
    private function getRecipes(
        ?int $userId = null,
        ?string $status = null,
        ?string $mealType = null,
        ?string $cuisineType = null,
        ?string $dietaryPreference = null,
        int $offset = 0,
        int $limit = 10
    ): array {  // Returns ['recipes' => [], 'total' => 0]
        try {
            [$query, $countQuery, $params] = $this->buildQuery($userId, $status, $mealType, $cuisineType, $dietaryPreference);

            // Fetch total count (before LIMIT/OFFSET)
            $countStmt = $this->connection->prepare($countQuery);
            foreach ($params as $paramName => $paramValue) {
                $countStmt->bindValue($paramName, $paramValue[0], $paramValue[1]);
            }
            $countStmt->execute();
            $total = (int)$countStmt->fetchColumn();


            // Fetch recipes (with LIMIT/OFFSET)
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = [$limit, PDO::PARAM_INT];
            $params[':offset'] = [$offset, PDO::PARAM_INT];

            $stmt = $this->connection->prepare($query);
            foreach ($params as $paramName => $paramValue) {
                $stmt->bindValue($paramName, $paramValue[0], $paramValue[1]);
            }
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $recipes = [];

            foreach ($rows as $row) {
                $recipes[] = $this->rowToRecipe($row);
            }

            return [
                'recipes' => $recipes,
                'total' => $total,
            ];

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        }
    }

    // helper function for building the query and pagination
    private function buildQuery(
        ?int $userId = null,
        ?string $status = null,
        ?string $mealType = null,
        ?string $cuisineType = null,
        ?string $dietaryPreference = null
    ): array {  // Returns [query, params]
        $query = "SELECT * FROM Recipe WHERE 1=1";
        $countQuery = "SELECT COUNT(*) FROM Recipe WHERE 1=1";
        $params = [];

        if ($userId !== null) {
            $query .= " AND userId = :userId";
            $countQuery .= " AND userId = :userId";
            $params[':userId'] = [$userId, PDO::PARAM_INT];
        }
        if ($status !== null) {
            $query .= " AND status = :status";
            $countQuery .= " AND status = :status";
            $params[':status'] = [$status, PDO::PARAM_STR];
        }
        if ($mealType !== null) {
            $query .= " AND mealType = :mealType";
            $countQuery .= " AND mealType = :mealType";
            $params[':mealType'] = [$mealType, PDO::PARAM_STR];
        }
        if ($cuisineType !== null) {
            $query .= " AND cuisineType = :cuisineType";
            $countQuery .= " AND cuisineType = :cuisineType";
            $params[':cuisineType'] = [$cuisineType, PDO::PARAM_STR];
        }
        if ($dietaryPreference !== null) {
            $query .= " AND dietaryPreference = :dietaryPreference";
            $countQuery .= " AND dietaryPreference = :dietaryPreference";
            $params[':dietaryPreference'] = [$dietaryPreference, PDO::PARAM_STR];
        }

        return [$query, $countQuery, $params];
    }

    // create, update and delete recipes
    public function createRecipe(Recipe $recipe): bool
    {
        $stmt = $this->connection->prepare("
        INSERT INTO Recipe (name, ingredients, instructions, imgPath, mealType, dietaryPreference, cuisineType, description, status, userId)
        VALUES (:name, :ingredients, :instructions, :imgPath, :mealType, :dietaryPreference, :cuisineType, :description, :status, :userId)
    ");

        $stmt->bindValue(':name', $recipe->getName());
        $stmt->bindValue(':ingredients', $recipe->getIngredients());
        $stmt->bindValue(':instructions', $recipe->getInstructions());
        $stmt->bindValue(':imgPath', $recipe->getImgPath());
        $stmt->bindValue(':mealType', $recipe->getMealType()->value);
        $stmt->bindValue(':dietaryPreference', $recipe->getDietaryPreference()->value);
        $stmt->bindValue(':cuisineType', $recipe->getCuisineType()->value);
        $stmt->bindValue(':description', $recipe->getDescription());
        $stmt->bindValue(':status', $recipe->getStatus()->value);
        $stmt->bindValue(':userId', $recipe->getUserId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function updateRecipe(Recipe $recipe): bool
    {
        $stmt = $this->connection->prepare("
        UPDATE Recipe
        SET name = :name, ingredients = :ingredients, instructions = :instructions, imgPath = :imgPath, 
            mealType = :mealType, dietaryPreference = :dietaryPreference, cuisineType = :cuisineType, 
            description = :description, status = :status
        WHERE id = :id
    ");

        $stmt->bindValue(':id', $recipe->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':name', $recipe->getName());
        $stmt->bindValue(':ingredients', $recipe->getIngredients());
        $stmt->bindValue(':instructions', $recipe->getInstructions());
        $stmt->bindValue(':imgPath', $recipe->getImgPath());
        $stmt->bindValue(':mealType', $recipe->getMealType()->value);
        $stmt->bindValue(':dietaryPreference', $recipe->getDietaryPreference()->value);
        $stmt->bindValue(':cuisineType', $recipe->getCuisineType()->value);
        $stmt->bindValue(':description', $recipe->getDescription());
        $stmt->bindValue(':status', $recipe->getStatus()->value);

        return $stmt->execute();
    }

    public function deleteRecipe(int $id): bool
    {
        $stmt = $this->connection->prepare("DELETE FROM Recipe WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // update recipe status (for admin)
    public function updateRecipeStatus(int $id, ApprovalStatus $status): bool
    {
        $stmt = $this->connection->prepare("UPDATE Recipe SET status = :status WHERE id = :id");
        $stmt->bindValue(':status', $status->value);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}