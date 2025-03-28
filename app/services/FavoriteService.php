<?php

namespace Services;

use Exception;
use Repositories\FavoriteRepository;
use Services\exceptions\NotFoundException;

class FavoriteService
{
    private FavoriteRepository $favoriteRepository;

    public function __construct()
    {
        $this->favoriteRepository = new FavoriteRepository();
    }

    public function addFavorite($userId, $recipeId): bool
    {
        if ($this->favoriteRepository->isFavorite($userId, $recipeId)) {
            throw new Exception("Recipe is already in favorites.");
        }
        return $this->favoriteRepository->addFavorite($userId, $recipeId);
    }

    public function removeFavorite($userId, $recipeId): bool
    {
        if (!$this->favoriteRepository->isFavorite($userId, $recipeId)) {
            throw new NotFoundException("Recipe is not in favorites.");
        }
        return $this->favoriteRepository->removeFavorite($userId, $recipeId);
    }

    public function isFavorite($userId, $recipeId): bool
    {
        return $this->favoriteRepository->isFavorite($userId, $recipeId);
    }

    public function getUserFavorites(int $userId, int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;
        return $this->favoriteRepository->getUserFavorites($userId, $offset, $limit);
    }
}