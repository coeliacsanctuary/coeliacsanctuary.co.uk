<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Recipes;

use App\Actions\Recipes\GetTopRecipesForHomepageAction;
use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeMetric;
use App\Resources\Recipes\RecipeSimpleCardViewResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetTopRecipesForHomePageActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withRecipes();
    }

    #[Test]
    public function itCanReturnACollectionOfRecipes(): void
    {
        $this->assertInstanceOf(AnonymousResourceCollection::class, $this->callAction(GetTopRecipesForHomepageAction::class));
    }

    #[Test]
    public function itOnlyReturnsTheRecipeAsACardResource(): void
    {
        $this->callAction(GetTopRecipesForHomepageAction::class)->each(function ($item): void {
            $this->assertInstanceOf(RecipeSimpleCardViewResource::class, $item);
        });
    }

    #[Test]
    public function itReturnsFourRecipes(): void
    {
        Recipe::query()->take(4)->get()->each(function (Recipe $recipe, int $index): void {
            $this->create(RecipeMetric::class, ['recipe_id' => $recipe->id, 'page_views' => ($index + 1) * 100]);
        });

        $this->assertCount(4, $this->callAction(GetTopRecipesForHomepageAction::class));
    }

    #[Test]
    public function itReturnsTheRecipesOrderedByPageViews(): void
    {
        $this->create(RecipeMetric::class, ['recipe_id' => 5, 'page_views' => 100]);
        $this->create(RecipeMetric::class, ['recipe_id' => 7, 'page_views' => 300]);
        $this->create(RecipeMetric::class, ['recipe_id' => 9, 'page_views' => 200]);

        $recipeTitles = $this->callAction(GetTopRecipesForHomepageAction::class)
            ->map(fn (RecipeSimpleCardViewResource $recipe) => $recipe->title)
            ->values()
            ->toArray();

        $this->assertSame(['Recipe 6', 'Recipe 8', 'Recipe 4', 'Recipe 3'], $recipeTitles);
    }

    #[Test]
    public function itOnlyConsidersMetricsFromTheLastDay(): void
    {
        $this->create(RecipeMetric::class, ['recipe_id' => 1, 'page_views' => 1000, 'date' => Carbon::now()->subDays(2)]);
        $this->create(RecipeMetric::class, ['recipe_id' => 5, 'page_views' => 300]);
        $this->create(RecipeMetric::class, ['recipe_id' => 7, 'page_views' => 200]);
        $this->create(RecipeMetric::class, ['recipe_id' => 9, 'page_views' => 100]);

        $recipeTitles = $this->callAction(GetTopRecipesForHomepageAction::class)
            ->map(fn (RecipeSimpleCardViewResource $recipe) => $recipe->title);

        $this->assertNotContains('Recipe 0', $recipeTitles);
    }

    #[Test]
    public function itDoesntReturnRecipesThatArentLive(): void
    {
        $this->create(RecipeMetric::class, ['recipe_id' => 1, 'page_views' => 300]);
        $this->create(RecipeMetric::class, ['recipe_id' => 2, 'page_views' => 200]);
        $this->create(RecipeMetric::class, ['recipe_id' => 3, 'page_views' => 100]);

        Recipe::query()->find(1)->update(['live' => false]);

        $recipeTitles = $this->callAction(GetTopRecipesForHomepageAction::class)
            ->map(fn (RecipeSimpleCardViewResource $recipe) => $recipe->title);

        $this->assertNotContains('Recipe 0', $recipeTitles);
        $this->assertContains('Recipe 1', $recipeTitles);
    }

    #[Test]
    public function itCachesTheRecipes(): void
    {
        $this->assertFalse(Cache::has('top-recipes'));

        $recipes = $this->callAction(GetTopRecipesForHomepageAction::class);

        $this->assertTrue(Cache::has('top-recipes'));
        $this->assertSame($recipes, Cache::get('top-recipes'));
    }

    #[Test]
    public function itLoadsTheRecipesFromTheCache(): void
    {
        DB::enableQueryLog();

        $this->callAction(GetTopRecipesForHomepageAction::class);

        // Recipes (with metrics sum subquery) and media relation
        $this->assertCount(2, DB::getQueryLog());

        $this->callAction(GetTopRecipesForHomepageAction::class);

        $this->assertCount(2, DB::getQueryLog());
    }
}
