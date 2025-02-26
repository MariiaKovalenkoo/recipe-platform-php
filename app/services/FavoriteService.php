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

    public function addFavorite($userId, $recipeId): bool
    {
        if($this->favoriteRepository->isFavorite($userId, $recipeId)) {
            return false;
        }
        return $this->favoriteRepository->addFavorite($userId, $recipeId);
    }

    public function removeFavorite($userId, $recipeId): bool
    {
        if(!$this->favoriteRepository->isFavorite($userId, $recipeId)) {
            return false;
        }
        return $this->favoriteRepository->removeFavorite($userId, $recipeId);
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