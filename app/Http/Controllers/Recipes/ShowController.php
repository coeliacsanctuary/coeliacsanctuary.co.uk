<?php

declare(strict_types=1);

namespace App\Http\Controllers\Recipes;

use App\Actions\Comments\GetCommentsForItemAction;
use App\Http\Response\Inertia;
use App\Models\Recipes\Recipe;
use App\Resources\Recipes\RecipeShowResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Inertia\Response;

class ShowController
{
    public function __invoke(Recipe $recipe, Inertia $inertia, GetCommentsForItemAction $getCommentsForItemAction): Response
    {
        $previous = URL::previous(route('recipe.index'));

        /** @var \Illuminate\Routing\Route | null $route */
        $route = Route::getRoutes()->match(Request::create($previous));

        $backLink = $route?->getName() === 'recipe.index' ? $previous : route('recipe.index');

        return $inertia
            ->title($recipe->title)
            ->metaDescription($recipe->meta_description)
            ->metaTags(explode(',', $recipe->meta_tags))
            ->metaImage($recipe->social_image)
            ->schema($recipe->schema()->toScript())
            ->alternateMetas([
                'article:publisher' => 'https://www.facebook.com/coeliacsanctuary',
                'article:section' => 'Food',
                'article:published_time' => $recipe->created_at,
                'article:modified_time' => $recipe->updated_at,
                'article:author' => 'Coeliac Sanctuary',
                'article.tags' => $recipe->meta_tags,
            ])
            ->metaFeed(route('recipe.feed'))
            ->render('Recipe/Show', [
                'recipe' => new RecipeShowResource($recipe),
                'comments' => fn () => $getCommentsForItemAction->handle($recipe),
                'backLink' => $backLink,
            ]);
    }
}
