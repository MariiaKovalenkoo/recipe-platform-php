<?php

namespace Controllers;

use Services\exceptions\NotFoundException;
use Services\FavoriteService;
use Exception;

class FavoriteController extends Controller
{
    private FavoriteService $favoriteService;

    public function __construct()
    {
        $this->favoriteService = new FavoriteService();
    }

    public function getUserFavorites(): void
    {
        try {
            $userId = $this->getCurrentUserId();

            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;

            $result = $this->favoriteService->getUserFavorites($userId, $page, $limit);
            $this->respondOk($result);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function isFavorite($recipeId): void
    {
        try {
            if (!$recipeId) {
                $this->respondWithError(400, "Missing recipeId");
                return;
            }
            $userId = $this->getCurrentUserId();
            $isFav = $this->favoriteService->isFavorite($userId, $recipeId);
            $this->respondOk(['isFavorite' => $isFav]);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function addFavorite(): void
    {
        try {
            $data = $this->getJsonData();

            if (!isset($data['recipeId'])) {
                $this->respondWithError(400, "Missing recipeId");
                return;
            }

            $userId = $this->getCurrentUserId();
            $success = $this->favoriteService->addFavorite($userId, $data['recipeId']);

            if ($success) {
                $this->respondOk(['message' => 'Added to favorites']);
            } else {
                $this->respondWithError(500, "An error occurred while adding the recipe to favorites.");
            }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function removeFavorite(int $recipeId): void
    {
        try {
            if (!$recipeId) {
                $this->respondWithError(400, "Missing recipe Id");
                return;
            }
            $userId = $this->getCurrentUserId();
            $success = $this->favoriteService->removeFavorite($userId, $recipeId);
            if ($success) {
                $this->respondOk(['message' => 'Removed from favorites']);
            } else {
                $this->respondWithError(500, "An error occurred while removing the recipe from favorites.");
            }
        } catch (NotFoundException $e) {
            $this->respondWithError(404, $e->getMessage());
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }
}