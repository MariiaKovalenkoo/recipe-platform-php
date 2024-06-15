<?php

namespace Repositories;

use Models\Recipe;
use PDO;
use PDOException;

class RecipeRepository extends Repository
{
    public function getAll($offset, $limit)
    {
        try {
            $query = "SELECT * FROM Recipe";
//            if (isset($limit) && isset($offset)) {
//                $query .= " LIMIT :limit OFFSET :offset ";
//            }
            $stmt = $this->connection->prepare($query);
//            if (isset($limit) && isset($offset)) {
//                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
//                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
//            }
            $stmt->execute();


            $recipes = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $recipes[] = $this->rowToRecipe($row);
            }

            return $recipes;
        }
        catch (PDOException $e) {
            echo $e;
        }
    }
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
        $recipe->setIsPublic($row['isPublic']);
        $recipe->setStatus($row['status']);
        $recipe->setUserId($row['userId']);
        return $recipe;
    }

}