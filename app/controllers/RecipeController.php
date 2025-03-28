<?php

namespace Controllers;

use Models\enums\ApprovalStatus;
use Models\enums\CuisineType;
use Models\enums\DietaryPreference;
use Models\enums\MealType;
use Models\Recipe;
use Services\exceptions\AccessDeniedException;
use Services\exceptions\BadRequestException;
use Services\exceptions\NotFoundException;
use Services\ImageService;
use Services\RecipeService;
use Exception;

class RecipeController extends Controller
{
    private RecipeService $service;

    function __construct()
    {
        $this->service = new RecipeService();
    }

    // Get public recipes (approved only) - any user can access
    public function getPublicRecipes(): void
    {
        try {
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;

            $status = 'Approved'; // only approved recipes are public

            $mealType = $_GET['mealType'] ?? null;
            $cuisineType = $_GET['cuisineType'] ?? null;
            $dietaryPreference = $_GET['dietaryPreference'] ?? null;

            $result = $this->service->getPublicRecipes($page, $limit, $status, $mealType, $cuisineType, $dietaryPreference);
            $this->respondOk($result);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Get user recipes
    public function getUserRecipes(): void
    {
        try {
            if (!isset($GLOBALS['current_user'])) {
                $this->respondWithError(401, "Please log in to view your recipes.");
                return;
            }
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $userId = $GLOBALS['current_user']->id;

            $status = $_GET['status'] ?? null;
            $mealType = $_GET['mealType'] ?? null;
            $cuisineType = $_GET['cuisineType'] ?? null;
            $dietaryPreference = $_GET['dietaryPreference'] ?? null;

            $result = $this->service->getUserRecipes($userId, $page, $limit, $status, $mealType, $cuisineType, $dietaryPreference);

            $this->respondOk($result);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Get all recipes (Admin only)
    public function getAllRecipes(): void
    {
        try {
            if (!isset($GLOBALS['current_user'])) {
                $this->respondWithError(401, "Unauthorized: User not authenticated.");
                return;
            }
            if ($GLOBALS['current_user']->role !== 'admin') {
                $this->respondWithError(403, "Forbidden: Admin access required.");
                return;
            }

            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;

            $status = $_GET['status'] ?? null;
            $mealType = $_GET['mealType'] ?? null;
            $cuisineType = $_GET['cuisineType'] ?? null;
            $dietaryPreference = $_GET['dietaryPreference'] ?? null;

            $result = $this->service->getAllRecipes($page, $limit, $status, $mealType, $cuisineType, $dietaryPreference);
            $this->respondOk($result);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Get a single recipe by ID - to see recipe details, you need to be logged in
    public function getRecipeById($id): void
    {
        try {
            if (!isset($GLOBALS['current_user'])) {
                $this->respondWithError(401, "Unauthorized: Please log in to view the full recipe.");
                return;
            }
            $recipe = $this->service->getRecipeById($id, $GLOBALS['current_user']->id, $GLOBALS['current_user']->role);

            $this->respondOk($recipe);
        } catch (NotFoundException $e) {
            $this->respondWithError(404, $e->getMessage());
        } catch (AccessDeniedException $e) {
            $this->respondWithError(403, $e->getMessage());
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Create a new recipe
    public function createRecipe(): void
    {
        try {
            if (!isset($GLOBALS['current_user'])) {
                $this->respondWithError(401, "Unauthorized: Please log in to create a recipe.");
                return;
            }

            $recipeId = $this->service->CreateRecipe($_POST, $_FILES, $GLOBALS['current_user']);

            $this->respondCreated([
                "message" => "Recipe created successfully",
                "id" => $recipeId
            ]);
        } catch (BadRequestException $e) {
            $this->respondWithError(400, $e->getMessage());
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Update a recipe
    public function updateRecipe(int $id): void
    {
        try {
            if (!isset($GLOBALS['current_user'])) {
                $this->respondWithError(401, "Unauthorized. Please log in to update a recipe.");
                return;
            }

            $postData = $_POST;
            $fileData = $_FILES;
            $currentUser = $GLOBALS['current_user'];
            $success = $this->service->updateRecipe($id, $postData, $fileData, (int)$currentUser->id, $currentUser->role);

            if ($success)
                $this->respondOk([
                    "message" => "Recipe updated successfully",
                ]);
            else {
                $this->respondWithError(500, "Failed to update recipe.");
            }

        } catch (NotFoundException $e) {
            $this->respondWithError(404, $e->getMessage());
        } catch (AccessDeniedException $e) {
            $this->respondWithError(403, $e->getMessage());
        } catch (BadRequestException $e) {
            $this->respondWithError(400, $e->getMessage());
        } catch (Exception $e) {
            $this->respondWithError(500, "An unexpected error occurred while updating the recipe. " . $e->getMessage());
        }
    }

    // Delete a recipe
    public function deleteRecipe(int $id): void
    {
        try {
            if (!isset($GLOBALS['current_user'])) {
                $this->respondWithError(401, "Unauthorized. Please log in to delete a recipe.");
                return;
            }

            if ($this->service->deleteRecipe($id, $GLOBALS['current_user']->id, $GLOBALS['current_user']->role))
                $this->respondOk(["message" => "Recipe deleted successfully"]);
            else
                $this->respondWithError(500, "Failed to delete recipe.");
        } catch (NotFoundException $e) {
            $this->respondWithError(404, $e->getMessage());
        } catch (AccessDeniedException $e) {
            $this->respondWithError(403, $e->getMessage());
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Approve/reject a recipe (Admin only)
    public function updateStatus($id): void
    {
        try {
            if (!isset($GLOBALS['current_user'])) {
                $this->respondWithError(401, "Unauthorized. Please log in.");
                return;
            }

            if ($GLOBALS['current_user']->role !== 'admin') {
                $this->respondWithError(403, "Unauthorized. Admin access required.");
                return;
            }

            $input = json_decode(file_get_contents("php://input"), true);
            $status = $input['status'] ?? null;

            if (!in_array($status, ['Approved', 'Rejected'])) {
                $this->respondWithError(400, "Invalid status value. Must be 'Approved' or 'Rejected'.");
                return;
            }

            $this->service->updateRecipeStatus((int)$id, $status);

            $this->respondOk(["message" => "Recipe status updated to {$status}."]);

        } catch (NotFoundException $e) {
            $this->respondWithError(404, $e->getMessage());
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }


    public function getFilters(): void
    {
        try {
            $dietaryPreferences = array_map(fn($case) => $case->value, DietaryPreference::cases());
            $mealTypes = array_map(fn($case) => $case->value, MealType::cases());
            $cuisineTypes = array_map(fn($case) => $case->value, CuisineType::cases());
            $status = array_map(fn($case) => $case->value, ApprovalStatus::cases());

            $this->respondOk([
                'dietaryPreferences' => $dietaryPreferences,
                'mealTypes' => $mealTypes,
                'cuisineTypes' => $cuisineTypes,
                'status' => $status,
            ]);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
            error_log("Error fetching filters: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
        }
    }
}