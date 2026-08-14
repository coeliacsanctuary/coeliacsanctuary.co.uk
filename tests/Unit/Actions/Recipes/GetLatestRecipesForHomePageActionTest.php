<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Recipes;

use App\Actions\Recipes\GetLatestRecipesForHomepageAction;
use App\Models\Recipes\Recipe;
use App\Resources\Recipes\RecipeSimpleCardViewResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetLatestRecipesForHomePageActionTest extends TestCase
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
        $this->assertInstanceOf(AnonymousResourceCollection::class, $this->callAction(GetLatestRecipesForHomepageAction::class));
    }

    #[Test]
    public function itOnlyReturnsTheRecipeAsACardResource(): void
    {
        $this->callAction(GetLatestRecipesForHomepageAction::class)->each(function ($item): void {
            $this->assertInstanceOf(RecipeSimpleCardViewResource::class, $item);
        });
    }

    #[Test]
    public function itReturnsFourRecipes(): void
    {
        $this->assertCount(4, $this->callAction(GetLatestRecipesForHomepageAction::class));
    }

    #[Test]
    public function itReturnsTheLatestRecipesFirst(): void
    {
        $recipeTitles = $this->callAction(GetLatestRecipesForHomepageAction::class)->map(fn (RecipeSimpleCardViewResource $recipe) => $recipe->title);

        $this->assertContains('Recipe 0', $recipeTitles);
        $this->assertContains('Recipe 1', $recipeTitles);
        $this->assertNotContains('Recipe 9', $recipeTitles);
    }

    #[Test]
    public function itDoesntReturnRecipesThatArentLive(): void
    {
        Recipe::query()->first()->update(['live' => false]);

        $recipeTitles = $this->callAction(GetLatestRecipesForHomepageAction::class)->map(fn (RecipeSimpleCardViewResource $recipe) => $recipe->title);

        $this->assertNotContains('Recipe 0', $recipeTitles);
        $this->assertContains('Recipe 2', $recipeTitles);
    }

    #[Test]
    public function itCachesTheRecipes(): void
    {
        $this->assertFalse(Cache::has(config('coeliac.cacheable.recipes.home')));

        $recipes = $this->callAction(GetLatestRecipesForHomepageAction::class);

        $this->assertTrue(Cache::has(config('coeliac.cacheable.recipes.home')));
        $this->assertSame($recipes, Cache::get(config('coeliac.cacheable.recipes.home')));
    }

    #[Test]
    public function itLoadsTheRecipesFromTheCache(): void
    {
        DB::enableQueryLog();

        $this->callAction(GetLatestRecipesForHomepageAction::class);

        // Recipes and media relation;
        $this->assertCount(2, DB::getQueryLog());

        $this->callAction(GetLatestRecipesForHomepageAction::class);

        $this->assertCount(2, DB::getQueryLog());
    }
}
