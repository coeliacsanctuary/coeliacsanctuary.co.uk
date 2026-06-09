<?php

declare(strict_types=1);

namespace Jpeters8889\RelatedRecipesSearch;

use App\Models\Recipes\Recipe;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class RelatedRecipesSearch extends Field
{
    /** @var string */
    public $component = 'related-recipes-search';

    public function resolve($resource, $attribute = null): void
    {
        if ( ! ($resource instanceof Recipe) || ! $resource->exists) {
            return;
        }

        $resource->loadMissing('relatedRecipes');

        $selected = $resource->relatedRecipes->map(fn (Recipe $recipe) => [
            'id' => $recipe->id,
            'title' => $recipe->title,
            'image' => $recipe->getFirstMediaUrl('primary') ?: $recipe->getFirstMediaUrl('square'),
        ]);

        $this->withMeta(['selected_recipes' => $selected]);
    }

    public function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute): void
    {
        if ( ! $request->exists($requestAttribute)) {
            return;
        }

        /** @var array<int> $ids */
        $ids = json_decode($request[$requestAttribute] ?? '[]', true) ?? [];

        $model->relatedRecipes()->sync($ids);
    }
}
