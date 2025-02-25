<?php

namespace Services;

use Models\Recipe;
use Repositories\RecipeRepository;
use Exception;

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

    public function getRecipeById(int $id): ?Recipe
    {
        return $this->repository->getRecipeById($id);
    }

    public function createRecipe(Recipe $recipe)
    {
        return $this->repository->createRecipe($recipe);
    }

    public function updateRecipe(Recipe $recipe, int $userId)
    {
        $existingRecipe = $this->repository->getRecipeById($recipe->getId());

        if (!$existingRecipe) {
            throw new Exception("Recipe not found.");
        }

        // Ensure the logged-in user is the owner of the recipe
        if ($existingRecipe->getUserId() !== $userId) {
            throw new Exception("Unauthorized to edit this recipe.");
        }

        return $this->repository->updateRecipe($recipe);
    }

    public function deleteRecipe(int $id, int $userId)
    {
        $existingRecipe = $this->repository->getRecipeById($id);

        if (!$existingRecipe) {
            throw new Exception("Recipe not found.");
        }

        // Ensure the logged-in user is the owner of the recipe
        if ($existingRecipe->getUserId() !== $userId) {
            throw new Exception("Unauthorized to delete this recipe.");
        }

        return $this->repository->deleteRecipe($id);
    }

    public function approveRecipe(int $id, int $adminId)
    {
        $recipe = $this->repository->getRecipeById($id);

        if (!$recipe) {
            throw new Exception("Recipe not found.");
        }

        // Ensure the user is an admin
        if (!$GLOBALS['current_user'] || $GLOBALS['current_user']->role !== 'admin') {
            throw new Exception("Unauthorized. Admin access required.");
        }

        return $this->repository->updateRecipeStatus($id, ApprovalStatus::APPROVED);
    }

    public function rejectRecipe(int $id, int $adminId)
    {
        $recipe = $this->repository->getRecipeById($id);

        if (!$recipe) {
            throw new Exception("Recipe not found.");
        }

        // Ensure the user is an admin
        if (!$GLOBALS['current_user'] || $GLOBALS['current_user']->role !== 'admin') {
            throw new Exception("Unauthorized. Admin access required.");
        }

        return $this->repository->updateRecipeStatus($id, ApprovalStatus::REJECTED);
    }
}