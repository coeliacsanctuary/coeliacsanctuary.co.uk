<?php

declare(strict_types=1);

namespace App\Actions\Recipes;

use App\Models\Recipes\Recipe;
use App\Resources\Recipes\RecipeSimpleCardViewResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GetLatestRecipesForHomepageAction
{
    public function handle(): AnonymousResourceCollection
    {
        /** @var string $key */
        $key = config('coeliac.cacheable.recipes.home');

        /** @var AnonymousResourceCollection $recipes */
        $recipes = Cache::rememberForever(
            $key,
            fn () => RecipeSimpleCardViewResource::collection(Recipe::query()
                ->take(4)
                ->latest()
                ->with(['media', 'nutrition'])
                ->get())
        );

        return $recipes;
    }
}
