<?php

declare(strict_types=1);

namespace App\ResourceCollections\Recipes;

use App\Resources\Recipes\RecipeDetailCardViewResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RecipeListCollection extends ResourceCollection
{
    public $collects = RecipeDetailCardViewResource::class;
}
