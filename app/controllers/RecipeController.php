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
            $filters = $this->getPaginationAndFilters();
            $filters['status'] = 'Approved'; //  only approved recipes are public

            $result = $this->service->getPublicRecipes(
                $filters['page'],
                $filters['limit'],
                $filters['status'],
                $filters['mealType'],
                $filters['cuisineType'],
                $filters['dietaryPreference']
            );

            $this->respondOk($result);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Get user recipes
    public function getUserRecipes(): void
    {
        try {
            $userId = $this->getCurrentUserId();
            $filters = $this->getPaginationAndFilters();

            $result = $this->service->getUserRecipes(
                $userId,
                $filters['page'],
                $filters['limit'],
                $filters['status'],
                $filters['mealType'],
                $filters['cuisineType'],
                $filters['dietaryPreference']
            );

            $this->respondOk($result);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    // Get all recipes (Admin only)
    public function getAllRecipes(): void
    {
        try {
            $filters = $this->getPaginationAndFilters();
            $result = $this->service->getAllRecipes(
                $filters['page'],
                $filters['limit'],
                $filters['status'],
                $filters['mealType'],
                $filters['cuisineType'],
                $filters['dietaryPreference']
            );

            $this->respondOk($result);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    private function getPaginationAndFilters(): array
    {
        return [
            'page' => $_GET['page'] ?? 1,
            'limit' => $_GET['limit'] ?? 10,
            'status' => $_GET['status'] ?? null,
            'mealType' => $_GET['mealType'] ?? null,
            'cuisineType' => $_GET['cuisineType'] ?? null,
            'dietaryPreference' => $_GET['dietaryPreference'] ?? null,
        ];
    }

    public function getRecipeById($recipeId): void
    {
        try {
            if (!$recipeId) {
                $this->respondWithError(400, "Missing recipeId");
                return;
            }
            $userId = $this->getCurrentUserId();
            $userRole = $this->getCurrentUserRole();
            $recipe = $this->service->getRecipeById($recipeId, $userId, $userRole);

            $this->respondOk($recipe);
        } catch (NotFoundException $e) {
            $this->respondWithError(404, $e->getMessage());
        } catch (AccessDeniedException $e) {
            $this->respondWithError(403, $e->getMessage());
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function createRecipe(): void
    {
        try {
            $userId = $this->getCurrentUserId();
            $userRole = $this->getCurrentUserRole();
            $data = $this->getPostedFormAndFiles();

            $recipeId = $this->service->CreateRecipe($data['form'], $data['files'], $userId, $userRole);

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

    public function updateRecipe(int $recipeId): void
    {
        try {
            if (!$recipeId) {
                $this->respondWithError(400, "Missing recipeId");
                return;
            }

            $userId = $this->getCurrentUserId();
            $userRole = $this->getCurrentUserRole();
            $data = $this->getPostedFormAndFiles();

            $success = $this->service->updateRecipe($recipeId, $data['form'], $data['files'], $userId, $userRole);

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

    public function deleteRecipe(int $recipeId): void
    {
        try {
            if (!$recipeId) {
                $this->respondWithError(400, "Missing recipeId");
                return;
            }

            $userId = $this->getCurrentUserId();
            $userRole = $this->getCurrentUserRole();

            if ($this->service->deleteRecipe($recipeId, $userId, $userRole))
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

    public function updateStatus($recipeId): void
    {
        try {
            if (!$recipeId) {
                $this->respondWithError(400, "Missing recipeId");
                return;
            }
            $input = $this->getJsonData();
            $status = $input['status'] ?? null;

            $this->service->updateRecipeStatus($recipeId, $status);
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
        }
    }
}