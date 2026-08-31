<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Recipes;

use App\Filament\Resources\MainSite\Recipes\RecipeResource;
use App\Filament\Resources\MainSite\Recipes\RelationManagers\CommentsRelationManager;
use App\Models\Recipes\Recipe;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipeResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itListsTheNewestRecipesFirst(): void
    {
        $recipes = $this->create(Recipe::class, 3);

        $this->assertSame(
            $recipes->pluck('id')->reverse()->values()->all(),
            RecipeResource::getEloquentQuery()->pluck('id')->all(),
        );
    }

    #[Test]
    public function itIncludesRecipesThatArentLive(): void
    {
        $this->create(Recipe::class, ['live' => true]);
        $this->build(Recipe::class)->notLive()->create();
        $this->build(Recipe::class)->draft()->create();

        $this->assertCount(3, RecipeResource::getEloquentQuery()->get());
    }

    #[Test]
    public function itTransformsTheStatusBeforeSaving(): void
    {
        $data = RecipeResource::mutateForSave([
            'status' => 'Live',
            'publish_at' => Carbon::now(),
            'title' => 'Gluten Free Victoria Sponge',
        ]);

        $this->assertTrue($data['live']);
        $this->assertNull($data['publish_at']);
        $this->assertArrayNotHasKey('status', $data);
        $this->assertSame('Gluten Free Victoria Sponge', $data['title']);
    }

    #[Test]
    public function itManagesComments(): void
    {
        $this->assertSame([CommentsRelationManager::class], RecipeResource::getRelations());
    }

    #[Test]
    public function itIsGloballySearchableByTitleAndSlug(): void
    {
        $this->assertSame(['title', 'slug'], RecipeResource::getGloballySearchableAttributes());
    }

    #[Test]
    public function itTitlesRecordsByTheirRecipeTitle(): void
    {
        $this->assertSame('title', RecipeResource::getRecordTitleAttribute());
    }

    #[Test]
    public function itRegistersTheListCreateEditAndMetricsPages(): void
    {
        $this->assertSame(['index', 'create', 'edit', 'metrics'], array_keys(RecipeResource::getPages()));
    }

    #[Test]
    public function itLinksToTheMetricsPageForARecipe(): void
    {
        $recipe = $this->create(Recipe::class);

        $this->assertStringEndsWith(
            "/admin/main-site/recipes/{$recipe->getRouteKey()}/metrics",
            RecipeResource::getUrl('metrics', ['record' => $recipe])
        );
    }
}
