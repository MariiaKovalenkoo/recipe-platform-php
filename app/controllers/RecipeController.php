<?php

namespace Controllers;

use Services\RecipeService;
use Exception;

class RecipeController extends Controller
{
    private RecipeService $service;

    function __construct()
    {
        $this->service = new RecipeService();
    }

    public function getPublicRecipes()
    {
        try {
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;

            $status = 'Approved'; // only approved recipes are public

            $mealType = isset($_GET['mealType']) && $_GET['mealType'] !== '' ? $_GET['mealType'] : null;
            $cuisineType = isset($_GET['cuisineType']) && $_GET['cuisineType'] !== '' ? $_GET['cuisineType'] : null;
            $dietaryPreference = isset($_GET['dietaryPreference']) && $_GET['dietaryPreference'] !== '' ? $_GET['dietaryPreference'] : null;

            $result = $this->service->getPublicRecipes($page, $limit, $status, $mealType, $cuisineType, $dietaryPreference);
            $this->respondOk($result);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Get user recipes
    public function getUserRecipes()
    {
        try {
            if (!isset($GLOBALS['current_user'])) {
                $this->respondWithError(401, "Unauthorized.");
                return;
            }
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $userId = $GLOBALS['current_user']->id;
            $status = $_GET['status'] ?? null; // Get the status!
            $mealType = $_GET['mealType'] ?? null;         // Get filter parameters
            $cuisineType = $_GET['cuisineType'] ?? null;
            $dietaryPreference = $_GET['dietaryPreference'] ?? null;

            $result = $this->service->getUserRecipes($userId, $page, $limit, $status, $mealType, $cuisineType, $dietaryPreference);

            $this->respondOk($result);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Get all recipes (Admin only)
    public function getAllRecipes()
    {
        try {
            if (!isset($GLOBALS['current_user']) || !isset($GLOBALS['current_user']->isAdmin) || !$GLOBALS['current_user']->isAdmin) {
                $this->respondWithError(403, "Unauthorized. Admin access required.");
                return;
            }

            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $status = $_GET['status'] ?? null;
            $mealType = $_GET['mealType'] ?? null;         // Get filter parameters
            $cuisineType = $_GET['cuisineType'] ?? null;
            $dietaryPreference = $_GET['dietaryPreference'] ?? null;

            $result = $this->service->getAllRecipes($page, $limit, $status, $mealType, $cuisineType, $dietaryPreference);
            $this->respondOk($result);
        } catch(Exception $e){
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Get a single recipe by ID
    public function getRecipeById($id): void
    {
        try {
            $recipe = $this->service->getRecipeById($id);

            if (!$recipe) {
                $this->respondWithError(404, "Recipe not found.");
                return;
            }

            $this->respondOk($recipe);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Create a new recipe
    public function createRecipe()
    {
        try {
            if (!isset($GLOBALS['current_user'])) {
                $this->respondWithError(401, "Unauthorized.");
                return;
            }

            $postedRecipe = $this->createObjectFromPostedJson("Models\\Recipe");
            $postedRecipe->setUserId($GLOBALS['current_user']->id);

            $this->service->createRecipe($postedRecipe);
            $this->respondOk(["message" => "Recipe created successfully"]);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Update a recipe
    public function updateRecipe($id)
    {
        try {
            if (!isset($GLOBALS['current_user'])) {
                $this->respondWithError(401, "Unauthorized.");
                return;
            }

            $postedRecipe = $this->createObjectFromPostedJson("Models\\Recipe");
            $postedRecipe->setId($id);

            $this->service->updateRecipe($postedRecipe, $GLOBALS['current_user']->id);
            $this->respondOk(["message" => "Recipe updated successfully"]);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Delete a recipe
    public function deleteRecipe($id)
    {
        try {
            if (!isset($GLOBALS['current_user'])) {
                $this->respondWithError(401, "Unauthorized.");
                return;
            }

            $this->service->deleteRecipe($id, $GLOBALS['current_user']->id);
            $this->respondOk(["message" => "Recipe deleted successfully"]);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Approve a recipe (Admin only)
    public function approveRecipe($id)
    {
        try {
            if (!$GLOBALS['current_user'] || $GLOBALS['current_user']->role !== 'admin') {
                $this->respondWithError(403, "Unauthorized. Admin access required.");
                return;
            }

            $this->service->approveRecipe($id, $GLOBALS['current_user']->id);
            $this->respondOk(["message" => "Recipe approved successfully."]);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Reject a recipe (Admin only)
    public function rejectRecipe($id)
    {
        try {
            if (!$GLOBALS['current_user'] || $GLOBALS['current_user']->role !== 'admin') {
                $this->respondWithError(403, "Unauthorized. Admin access required.");
                return;
            }

            $this->service->rejectRecipe($id, $GLOBALS['current_user']->id);
            $this->respondOk(["message" => "Recipe rejected successfully."]);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }
}