<?php

use Bramus\Router\Router;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

error_reporting(E_ALL);
ini_set("display_errors", 1);

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/jwt_middleware.php';

$router = new Router();

$router->setNamespace('Controllers');

// routes that do not require authentication
$router->post('/users/login', 'UserController@login');
$router->post('/users/register', 'UserController@register');
$router->get('/recipes/public', 'RecipeController@getPublicRecipes'); // get public recipes
$router->get('/recipes/filters', 'RecipeController@getFilters'); // get recipe filters

// recipe routes (require authentication)
$router->before('GET|POST|PUT|DELETE', '/recipes/(?!public|filters).*', 'checkJwtMiddleware');
$router->get('/recipes/mine', 'RecipeController@getUserRecipes'); // get user recipes
$router->get('/recipes/{id}', 'RecipeController@getRecipeById'); // view a single recipe
$router->post('/recipes', 'RecipeController@createRecipe'); // Create a recipe
$router->post('/recipes/{id}', 'RecipeController@updateRecipe'); // Update a recipe
$router->delete('/recipes/{id}', 'RecipeController@deleteRecipe'); // Delete a recipe

$router->before('GET|POST|DELETE', '/favorites.*', 'checkJwtMiddleware');
$router->get('/favorites/(\d+)', 'FavoriteController@isFavorite'); // check if a recipe is a favorite
$router->post('/favorites', 'FavoriteController@addFavorite'); // add a recipe to favorites
$router->delete('/favorites/{id}', 'FavoriteController@removeFavorite'); // remove a recipe from favorites
$router->get('/favorites', 'FavoriteController@getUserFavorites'); // get user favorites

// admin routes (require authentication)
$router->before('GET|PUT|POST|DELETE', '/admin/recipes.*', 'checkAdminMiddleware');
$router->get('/admin/recipes', 'RecipeController@getAllRecipes'); // get all recipes
$router->put('/admin/recipes/(\d+)/status', 'RecipeController@updateStatus'); // update recipe status

$router->run();