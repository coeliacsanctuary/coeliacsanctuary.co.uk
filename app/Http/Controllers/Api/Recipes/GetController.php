<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Recipes;

use App\Models\Recipes\Recipe;
use App\Resources\Recipes\RecipeApiResource;

class GetController
{
    public function __invoke(Recipe $recipe): RecipeApiResource
    {
        return RecipeApiResource::make($recipe);
    }
}
