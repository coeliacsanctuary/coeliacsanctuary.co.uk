<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Recipes;

use App\Actions\Recipes\GetRecipesForIndexAction;
use App\ResourceCollections\Recipes\RecipeApiCollection;
use Illuminate\Http\Request;

class IndexController
{
    public function __invoke(Request $request, GetRecipesForIndexAction $getRecipesForIndexAction): RecipeApiCollection
    {
        $search = $request->has('search') ? $request->string('search')->toString() : null;

        return $getRecipesForIndexAction->handle(resource: RecipeApiCollection::class, search: $search);
    }
}
