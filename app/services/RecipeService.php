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
    private ImageService $imageService;

    private FavoriteService $favoriteService;

    function __construct()
    {
        $this->repository = new RecipeRepository();
        $this->imageService = new ImageService();
        $this->favoriteService = new FavoriteService();
    }

    // GET
    public function getAllRecipes(int $page, int $limit, ?string $status = null, ?string $mealType = null, ?string $cuisineType = null, ?string $dietaryPreference = null): array
    {
        $offset = ($page - 1) * $limit;
        return $this->repository->getAllRecipes($offset, $limit, $status, $mealType, $cuisineType, $dietaryPreference);
    }

    public function getPublicRecipes(int $page, int $limit, ?string $status = null, ?string $mealType = null, ?string $cuisineType = null, ?string $dietaryPreference = null): array
    {
        $offset = ($page - 1) * $limit;
        return $this->repository->getPublicRecipes($offset, $limit, $status, $mealType, $cuisineType, $dietaryPreference);
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

        $isFavorite = $this->favoriteService->isFavorite($userId, $id);
        $recipe->setIsFavorite($isFavorite);

        return $recipe;
    }

    private function validateFields(Recipe $recipe, array $postData, string $userRole): void
    {
        $fields = [
            'name',
            'description',
            'ingredients',
            'instructions',
            'mealType',
            'cuisineType',
            'dietaryPreference',
            'status',
        ];

        foreach ($fields as $key) {
            $value = isset($postData[$key]) ? trim($postData[$key]) : '';

            if ($value === '') {
                throw new BadRequestException("$key is required.");
            }

            if ($key === 'status' && $userRole !== 'admin') {
                $value = $value === ApprovalStatus::PRIVATE->value
                    ? ApprovalStatus::PRIVATE->value
                    : ApprovalStatus::PENDING->value;
            }

            $setter = 'set' . ucfirst($key);
            if (method_exists($recipe, $setter)) {
                $recipe->$setter($value);
            }
        }
    }

    public function createRecipe(array $postData, array $fileData, int $userId, string $userRole): int
    {
        $recipe = new Recipe();
        $this->validateFields($recipe, $postData, $userRole);
        $recipe->setUserId($userId);

        if (!isset($fileData['image']) || $fileData['image']['error'] !== UPLOAD_ERR_OK) {
            throw new BadRequestException("Recipe image is required and was not uploaded.");
        }

        $imagePath = $this->imageService->uploadImage($fileData['image']);
        $recipe->setImgPath($imagePath);

        $recipeId = $this->repository->createRecipe($recipe);
        if (!$recipeId) {
            throw new Exception("Failed to create recipe.");
        }

        return $recipeId;
    }

    public function updateRecipe(int $id, array $postData, array $fileData, int $userId, string $role): bool
    {
        $existingRecipe = $this->repository->getRecipeById($id);

        if (!$existingRecipe) {
            throw new NotFoundException("Recipe with ID {$id} not found.");
        }

        if ($existingRecipe->getUserId() !== $userId && $role !== 'admin') {
            throw new AccessDeniedException("You are not authorized to edit this recipe.");
        }

        $oldImagePath = $existingRecipe->getImgPath();
        $imageUpdated = false;
        $newImagePath = null;

        $this->validateFields($existingRecipe, $postData, $role);

        if (isset($fileData['image']) && $fileData['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $newImagePath = $this->imageService->uploadImage($fileData['image']);
                $existingRecipe->setImgPath($newImagePath);
                $imageUpdated = true;
            } catch (Exception $e) {
                throw new Exception("Failed to upload new image: " . $e->getMessage());
            }
        }

        $success = $this->repository->updateRecipe($existingRecipe);

        if (!$success) {
            if ($imageUpdated && $newImagePath) {
                $this->imageService->deleteImage($newImagePath);
            }
            throw new Exception("Failed to save recipe updates to the database.");
        }

        if ($success && $imageUpdated && $oldImagePath && $oldImagePath !== $newImagePath) {
            try {
                $this->imageService->deleteImage($oldImagePath);
            } catch (Exception $e) {
                error_log("Failed to delete old image '{$oldImagePath}' after updating recipe ID {$id}: " . $e->getMessage(), 3, __DIR__ . '/../error_log.log');
            }
        }

        return $success;
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

        $imgPath = $existingRecipe->getImgPath();
        if (!empty($imgPath)) {
            $this->imageService->deleteImage($imgPath);
        }

        return $this->repository->deleteRecipe($id);
    }

    public function updateRecipeStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['Approved', 'Rejected'])) {
            throw new BadRequestException("Invalid status value. Must be 'Approved' or 'Rejected'.");
        }

        $recipe = $this->repository->getRecipeById($id);

        if (!$recipe) {
            throw new NotFoundException("Recipe not found.");
        }

        $statusEnum = ApprovalStatus::from($status);
        return $this->repository->updateRecipeStatus($id, $statusEnum->value);
    }
}