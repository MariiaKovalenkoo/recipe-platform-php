<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

error_reporting(E_ALL);
ini_set("display_errors", 1);

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/jwt_middleware.php';

$router = new \Bramus\Router\Router();

$router->setNamespace('Controllers');

// Define routes that do not require authentication
$router->post('/users/login', 'UserController@login');
$router->post('/users/register', 'UserController@register');

// Public recipes route (No JWT authentication required)
$router->get('/public', 'RecipeController@getPublicRecipes');

// All other /recipes routes (Requires JWT authentication)
$router->before('GET|POST|PUT|DELETE', '/recipes.*', 'checkJwtMiddleware');

$router->get('/recipes/mine', 'RecipeController@getUserRecipes');

$router->get('/recipes/{id}', 'RecipeController@getRecipeById'); // View a single recipe
$router->post('/recipes', 'RecipeController@createRecipe'); // Create a recipe
$router->put('/recipes/{id}', 'RecipeController@updateRecipe'); // Update a recipe
$router->delete('/recipes/{id}', 'RecipeController@deleteRecipe'); // Delete a recipe


// Require authentication for admin-only routes
$router->before('GET|PUT', '/admin/recipes.*', 'checkJwtMiddleware');

// Get all recipes (Admin only)
$router->get('/admin/recipes', 'RecipeController@getAllRecipes');

// Get recipes by status (Admin only)
//$router->get('/admin/recipes/status/(\w+)', 'RecipeController@getRecipesByStatus');

// Approve a recipe (Admin only)
$router->put('/admin/recipes/(\d+)/approve', 'RecipeController@approveRecipe');

// Reject a recipe (Admin only)
$router->put('/admin/recipes/(\d+)/reject', 'RecipeController@rejectRecipe');


// Run it!
$router->run();