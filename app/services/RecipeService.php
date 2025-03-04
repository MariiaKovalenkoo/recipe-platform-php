<?php

namespace Services;

use Models\enums\ApprovalStatus;
use Models\Recipe;
use Repositories\RecipeRepository;
use Exception;
use Services\exceptions\AccessDeniedException;
use Services\exceptions\BadRequestException;
use Services\exceptions\NotFoundException;

class RecipeService
{
    private RecipeRepository $repository;
    //private ImageService $imageService;

    function __construct()
    {
        $this->repository = new RecipeRepository();
       // $this->imageService = new ImageService();
    }

    // GET
    public function getAllRecipes(int $page, int $limit, ?string $status = null, ?string $mealType = null, ?string $cuisineType = null, ?string $dietaryPreference = null): array
    {
        $offset = ($page - 1) * $limit;
        return $this->repository->getAllRecipes($offset, $limit, $status, $mealType, $cuisineType, $dietaryPreference);
    }

    public function getPublicRecipes(int $page, int $limit, string $status = null, ?string $mealType = null, ?string $cuisineType = null, ?string $dietaryPreference = null): array
    {
        $offset = ($page - 1) * $limit;
        return  $this->repository->getPublicRecipes($offset, $limit, $status, $mealType, $cuisineType, $dietaryPreference);
    }

    public function getUserRecipes(int $userId, int $page, int $limit, ?string $status = null, ?string $mealType = null, ?string $cuisineType = null, ?string $dietaryPreference = null): array
    {
        $offset = ($page - 1) * $limit;
        return $this->repository->getUserRecipes($userId, $offset, $limit, $status, $mealType, $cuisineType, $dietaryPreference);
    }

    public function getRecipeById($id, $userId, $role): Recipe
    {
        $recipe = $this->repository->getRecipeById($id);
        if (!$recipe) {
            throw new NotFoundException("Recipe not found.");
        }

        if ($recipe->getStatus() !== ApprovalStatus::APPROVED && $recipe->getUserId() != $userId && $role !== 'admin') {  // Check ownership / admin check
            throw new AccessDeniedException("Forbidden: You do not have permission to view this recipe.");
        }

        return $recipe;
    }

    public function createRecipe(Recipe $recipe, string $role): int
    {
        // 1. check required fields
        try {
            $this->validateRecipe($recipe);
        } catch (Exception $e) {
            throw new BadRequestException($e->getMessage());
        }

        // 2. recipe status check
        // if not admin, status can be only private or pending
        if ($role !== 'admin') {
            if ($recipe->getStatus() !== ApprovalStatus::PRIVATE) {
                $recipe->setStatus(ApprovalStatus::PENDING->value);
            }
        }

        // 3. handle image upload
        // image is required
        // $recipe->setImgPath($this->imageService->uploadImage($recipe->getImgPath()));

        // 4. create recipe
        $recipeId = $this->repository->createRecipe($recipe);
        if (!$recipeId) {
            throw new Exception("Failed to create recipe.");
        }
        return $recipeId;
    }

    public function updateRecipe(Recipe $recipe, int $userId, string $role): bool
    {
        $existingRecipe = $this->repository->getRecipeById($recipe->getId());

        if (!$existingRecipe) {
            throw new NotFoundException("Recipe not found.");
        }

        // Ensure the logged-in user is the owner of the recipe
        if ($existingRecipe->getUserId() !== $userId) {
            throw new AccessDeniedException("Unauthorized to edit this recipe.");
        }

        // check required fields
        try {
            $this->validateRecipe($recipe);
        } catch (Exception $e) {
            throw new BadRequestException($e->getMessage());
        }

        // handle image upload??

        // check status change
        if ($role !== 'admin') {
            if ($recipe->getStatus() !== ApprovalStatus::PRIVATE) {
                $recipe->setStatus(ApprovalStatus::PENDING->value);
            }
        }

        return $this->repository->updateRecipe($recipe);
    }

    public function deleteRecipe(int $id, int $userId, string $role): bool
    {
        $existingRecipe = $this->repository->getRecipeById($id);

        if (!$existingRecipe) {
            throw new NotFoundException("Recipe not found.");
        }

        // Ensure the logged-in user is the owner of the recipe
        if ($existingRecipe->getUserId() !== $userId && $role !== 'admin') {
            throw new AccessDeniedException("Unauthorized to delete this recipe.");
        }

        return $this->repository->deleteRecipe($id);
    }

    public function approveRecipe(int $id): bool
    {
        $recipe = $this->repository->getRecipeById($id);

        if (!$recipe) {
            throw new NotFoundException("Recipe not found.");
        }

        return $this->repository->updateRecipeStatus($id, ApprovalStatus::APPROVED->value);
    }

    public function rejectRecipe(int $id): bool
    {
        $recipe = $this->repository->getRecipeById($id);

        if (!$recipe) {
            throw new NotFoundException("Recipe not found.");
        }

        return $this->repository->updateRecipeStatus($id, ApprovalStatus::REJECTED->value);
    }

    private function validateRecipe(Recipe $recipe): void
    {
        if (empty($recipe->getName())) {
            throw new Exception("Name is required.");
        }

        if (empty($recipe->getIngredients())) {
            throw new Exception("Ingredients are required.");
        }

        if (empty($recipe->getInstructions())) {
            throw new Exception("Instructions are required.");
        }

        if (empty($recipe->getMealType())) {
            throw new Exception("Meal type is required.");
        }

        if (empty($recipe->getDietaryPreference())) {
            throw new Exception("Dietary preference is required.");
        }

        if (empty($recipe->getCuisineType())) {
            throw new Exception("Cuisine type is required.");
        }

        if (empty($recipe->getDescription())) {
            throw new Exception("Description is required.");
        }

        if (empty($recipe->getStatus())) {
            throw new Exception("Status is required.");
        }
    }
}