<?php

namespace Services;

use Repositories\FavoriteRepository;

class FavoriteService
{
    private FavoriteRepository $favoriteRepository;

    public function __construct()
    {
        $this->favoriteRepository = new FavoriteRepository();
    }

    public function addToFavorite($userId, $recipeId): bool
    {
        return $this->favoriteRepository->addToFavorite($userId, $recipeId);
    }

    public function removeFromFavorite($userId, $recipeId): bool
    {
        return $this->favoriteRepository->removeFromFavorite($userId, $recipeId);
    }

    public function isFavorite($userId, $recipeId): bool
    {
        return $this->favoriteRepository->isFavorite($userId, $recipeId);
    }

    public function getFavoritesByUser($userId): array
    {
        return $this->favoriteRepository->getFavoritesByUser($userId);
    }
}