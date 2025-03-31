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
    public function getAllRecipes(int $offset = 0, int $limit = 10, ?string $status = null, ?string $mealType = null, ?string $cuisineType = null, ?string $dietaryPreference = null): array
    {
        return $this->getRecipes(null, $status, $mealType, $cuisineType, $dietaryPreference, $offset, $limit);
    }

    // get public recipes (approved only)
    public function getPublicRecipes(int $offset = 0, int $limit = 10, ?string $status = null, ?string $mealType = null, ?string $cuisineType = null, ?string $dietaryPreference = null): array
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

            if (!$row) {
                return null;
            }

            return $this->rowToRecipe($row);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    // helper function to convert a row from the database to a Recipe object
    private function rowToRecipe($row): Recipe
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
        ?int    $userId = null,
        ?string $status = null,
        ?string $mealType = null,
        ?string $cuisineType = null,
        ?string $dietaryPreference = null,
        int     $offset = 0,
        int     $limit = 10
    ): array
    {  // Returns ['recipes' => [], 'total' => 0]
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
            $query .= " ORDER BY name ASC LIMIT :limit OFFSET :offset";
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
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    // helper function for building the query and pagination
    private function buildQuery(
        ?int $userId = null,
        ?string $status = null,
        ?string $mealType = null,
        ?string $cuisineType = null,
        ?string $dietaryPreference = null
    ): array {
        $query = "SELECT * FROM Recipe WHERE 1=1";
        $countQuery = "SELECT COUNT(*) FROM Recipe WHERE 1=1";
        $params = [];

        // Define filters and their values/types
        $filters = [
            'userId' => [$userId, PDO::PARAM_INT],
            'status' => [$status, PDO::PARAM_STR],
            'mealType' => [$mealType, PDO::PARAM_STR],
            'cuisineType' => [$cuisineType, PDO::PARAM_STR],
            'dietaryPreference' => [$dietaryPreference, PDO::PARAM_STR],
        ];

        foreach ($filters as $field => [$value, $type]) {
            if ($value !== null) {
                $query .= " AND {$field} = :{$field}";
                $countQuery .= " AND {$field} = :{$field}";
                $params[":{$field}"] = [$value, $type];
            }
        }

        return [$query, $countQuery, $params];
    }

    // create, update and delete recipes
    public function createRecipe(Recipe $recipe): ?int
    {
        try {
            $fields = $this->extractRecipeFields($recipe);
            $fields['userId'] = $recipe->getUserId();

            $columns = implode(', ', array_keys($fields));
            $placeholders = ':' . implode(', :', array_keys($fields));

            $stmt = $this->connection->prepare("
            INSERT INTO Recipe ($columns)
            VALUES ($placeholders)
        ");

            foreach ($fields as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            if (!$stmt->execute()) {
                return null;
            }

            return (int)$this->connection->lastInsertId();

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        }
    }

    public function updateRecipe(Recipe $recipe): bool
    {
        try {
            $fields = $this->extractRecipeFields($recipe);
            $setParts = [];
            foreach ($fields as $key => $value) {
                $setParts[] = "$key = :$key";
            }
            $setClause = implode(', ', $setParts);

            $stmt = $this->connection->prepare("
            UPDATE Recipe SET $setClause WHERE id = :id
        ");

            foreach ($fields as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':id', $recipe->getId());

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        }
    }

    private function extractRecipeFields(Recipe $recipe): array
    {
        return [
            'name' => $recipe->getName(),
            'ingredients' => $recipe->getIngredients(),
            'instructions' => $recipe->getInstructions(),
            'imgPath' => $recipe->getImgPath(),
            'mealType' => $recipe->getMealType()->value,
            'dietaryPreference' => $recipe->getDietaryPreference()->value,
            'cuisineType' => $recipe->getCuisineType()->value,
            'description' => $recipe->getDescription(),
            'status' => $recipe->getStatus()->value,
        ];
    }

    public function deleteRecipe(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM Recipe WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    // update recipe status (for admin)
    public function updateRecipeStatus(int $id, string $status): bool
    {
        try {
            $stmt = $this->connection->prepare("UPDATE Recipe SET status = :status WHERE id = :id");
            $stmt->bindValue(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            throw new Exception("An error occurred while accessing the database: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }
}