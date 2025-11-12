<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\Recipes;

use App\Http\Api\V1\Resources\Recipes\RecipeResource;
use App\Models\Recipes\Recipe;

class IndexController
{
    public function __invoke(): array
    {
        $recipes = Recipe::query()
            ->with(['media'])
            ->latest()
            ->paginate(12);

        return [
            'data' => RecipeResource::collection($recipes),
        ];
    }
}
