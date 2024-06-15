<?php

namespace Controllers;

use Services\RecipeService;

class AdminController extends Controller
{

    public RecipeService $recipeService;

    public function __construct()
    {
        $this->recipeService = new RecipeService();
    }

    public function approveRecipe($id)
    {
        $this->recipeService->approveRecipe($id);
        header('Location: /admin');
    }

    public function rejectRecipe($id)
    {
        $this->recipeService->rejectRecipe($id);
        header('Location: /admin');
    }

    public function getRecipesByStatus($status)
    {
        $recipes = $this->recipeService->getRecipesByStatus($status);
        $this->respond($recipes);
    }

    public function getAllRecipes()
    {
        $recipes = $this->recipeService->getAllRecipes();
        $this->respond($recipes);
    }
}