<?php

declare(strict_types=1);

namespace App\ResourceCollections\Recipes;

use App\Resources\Recipes\RecipeApiResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RecipeApiCollection extends ResourceCollection
{
    public $collects = RecipeApiResource::class;

    /** @return array{data: mixed} */
    public function toArray(Request $request)
    {
        return ['data' => parent::toArray($request)];
    }
}
