<?php

declare(strict_types=1);

namespace App\Actions\Recipes;

use App\Models\Recipes\Recipe;
use App\Resources\Recipes\RecipeSimpleCardViewResource;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GetTopRecipesForHomepageAction
{
    public function handle(): AnonymousResourceCollection
    {
        /** @var AnonymousResourceCollection $recipes */
        $recipes = Cache::flexible(
            'top-recipes',
            [CarbonInterval::minutes(60), CarbonInterval::minutes(5)],
            fn () => RecipeSimpleCardViewResource::collection(Recipe::query()
                ->withSum(
                    /** @phpstan-ignore-next-line  */
                    ['metrics' => fn (Builder $query) => $query->where('date', '>=', Carbon::now()->subDay()->startOfDay())],
                    'page_views',
                )
                ->take(4)
                ->orderBy('metrics_sum_page_views', 'desc')
                ->with(['media'])
                ->get())
        );

        return $recipes;
    }
}
