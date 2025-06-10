<?php

declare(strict_types=1);

namespace App\Http\Controllers\Recipes;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Actions\Recipes\GetRecipeFiltersForIndexAction;
use App\Actions\Recipes\GetRecipesForIndexAction;
use App\Http\Response\Inertia;
use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeMeal;
use Illuminate\Http\Request;
use Inertia\Response;

class IndexController
{
    public function __invoke(
        Inertia $inertia,
        Request $request,
        GetRecipesForIndexAction $getRecipesForIndexAction,
        GetRecipeFiltersForIndexAction $getRecipeFiltersForIndexAction,
        GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction,
    ): Response {
        /** @var string[] $features */
        $features = $request->string('features', '')->explode(',')->filter()->toArray();

        /** @var string[] $meals */
        $meals = $request->string('meals', '')->explode(',')->filter()->toArray();

        /** @var string[] $freeFrom */
        $freeFrom = $request->string('freeFrom', '')->explode(',')->filter()->toArray();

        $filters = ['features' => $features, 'meals' => $meals, 'freeFrom' => $freeFrom];

        return $inertia
            ->title('Gluten Free Recipes')
            ->metaDescription('Coeliac Sanctuary gluten free recipe list, all of our fabulous gluten free recipes which I have been tried and tested! ')
            ->metaTags(['coeliac sanctuary recipes', 'recipe index', 'recipe list', 'gluten free recipes', 'recipes', 'coeliac recipes'])
            ->metaImage($getOpenGraphImageForRouteAction->handle('recipe'))
            ->metaFeed(route('recipe.feed'))
            ->render('Recipe/Index', [
                'recipes' => fn () => $getRecipesForIndexAction->handle($filters),
                'features' => fn () => $getRecipeFiltersForIndexAction->handle(RecipeFeature::class, $filters),
                'meals' => fn () => $getRecipeFiltersForIndexAction->handle(RecipeMeal::class, $filters),
                'freeFrom' => fn () => $getRecipeFiltersForIndexAction->handle(RecipeAllergen::class, $filters),
                'setFilters' => fn () => $filters,
            ]);
    }
}
