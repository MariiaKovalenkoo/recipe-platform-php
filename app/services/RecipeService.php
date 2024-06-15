<?php

namespace Services;

use Repositories\RecipeRepository;
class RecipeService
{
    private RecipeRepository $repository;

    function __construct()
    {
        $this->repository = new RecipeRepository();
    }

    public function getAll($offset, $limit)
    {
        return $this->repository->getAll($offset, $limit);
    }

}