<?php

declare(strict_types=1);

use App\Models\Recipes\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/search', function (Request $request): array {
    $term = $request->string('term')->toString();

    if ($term === '') {
        return [];
    }

    /** @var array<int> $excludedIds */
    $excludedIds = $request->input('excluded_ids', []);

    return Recipe::query()
        ->whereLike('title', "%{$term}%")
        ->whereNotIn('id', $excludedIds)
        ->limit(10)
        ->get()
        ->map(fn (Recipe $recipe) => [
            'id' => $recipe->id,
            'title' => $recipe->title,
            'image' => $recipe->getFirstMediaUrl('primary') ?: $recipe->getFirstMediaUrl('square'),
        ])
        ->all();
});
