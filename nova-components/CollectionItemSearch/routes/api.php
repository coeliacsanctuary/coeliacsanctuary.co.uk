<?php

declare(strict_types=1);

use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Jpeters8889\CollectionItemSearch\SearchResult;

Route::post('/search', function (Request $request): array {
    $type = (string) $request->input('type', '');
    $term = (string) $request->input('term', '');

    if ($type === '' || $term === '') {
        return [];
    }

    return match ($type) {
        Blog::class => Blog::where(fn ($q) => $q->whereLike('title', "%{$term}%")->orWhereLike('slug', "%{$term}%"))
            ->with('media')
            ->limit(10)
            ->get()
            ->map(fn (Blog $blog) => SearchResult::fromModel($blog, $type))
            ->values()
            ->all(),
        Recipe::class => Recipe::where(fn ($q) => $q->whereLike('title', "%{$term}%"))
            ->with('media')
            ->limit(10)
            ->get()
            ->map(fn (Recipe $recipe) => SearchResult::fromModel($recipe, $type))
            ->values()
            ->all(),
        Eatery::class => Eatery::where(fn ($q) => $q->whereLike('name', "%{$term}%")->orWhereLike('info', "%{$term}%"))
            ->limit(10)
            ->get()
            ->map(fn (Eatery $eatery) => SearchResult::fromModel($eatery, $type))
            ->values()
            ->all(),
        NationwideBranch::class => NationwideBranch::whereLike('name', "%{$term}%")
            ->with('eatery')
            ->limit(10)
            ->get()
            ->map(fn (NationwideBranch $branch) => SearchResult::fromModel($branch, $type))
            ->values()
            ->all(),
        default => [],
    };
});
