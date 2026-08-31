<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Recipes\Tables;

use App\Filament\Resources\MainSite\Recipes\Pages\ListRecipes;
use App\Filament\Resources\MainSite\Recipes\RecipeResource;
use App\Models\Recipes\Recipe;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipesTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itShowsEveryRecipeWhateverItsStatus(): void
    {
        $live = $this->create(Recipe::class, ['live' => true]);
        $notLive = $this->build(Recipe::class)->notLive()->create();
        $draft = $this->build(Recipe::class)->draft()->create();

        Livewire::test(ListRecipes::class)->assertCanSeeTableRecords([$live, $notLive, $draft]);
    }

    #[Test]
    public function itShowsTheNewestRecipesFirst(): void
    {
        $recipes = $this->create(Recipe::class, 3);

        Livewire::test(ListRecipes::class)->assertCanSeeTableRecords($recipes->reverse()->values(), inOrder: true);
    }

    #[Test]
    #[DataProvider('columns')]
    public function itShowsTheRecipeColumns(string $column): void
    {
        $this->create(Recipe::class);

        Livewire::test(ListRecipes::class)->assertTableColumnExists($column);
    }

    public static function columns(): array
    {
        return [
            'id' => ['id'],
            'title' => ['title'],
            'status' => ['status'],
            'created at' => ['created_at'],
            'publish at' => ['publish_at'],
        ];
    }

    #[Test]
    public function itLabelsTheIdColumn(): void
    {
        Livewire::test(ListRecipes::class)
            ->assertTableColumnExists('id', fn (TextColumn $column): bool => $column->getLabel() === 'ID');
    }

    #[Test]
    #[DataProvider('searchableColumns')]
    public function itCanBeSearchedByColumn(string $column): void
    {
        Livewire::test(ListRecipes::class)
            ->assertTableColumnExists($column, fn (TextColumn $c): bool => $c->isSearchable());
    }

    public static function searchableColumns(): array
    {
        return [
            'id' => ['id'],
            'title' => ['title'],
        ];
    }

    #[Test]
    public function itFindsARecipeByTitle(): void
    {
        $wanted = $this->create(Recipe::class, ['title' => 'Gluten Free Victoria Sponge']);
        $other = $this->create(Recipe::class, ['title' => 'Gluten Free Yorkshire Puddings']);

        Livewire::test(ListRecipes::class)
            ->searchTable('Victoria Sponge')
            ->assertCanSeeTableRecords([$wanted])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itFindsARecipeById(): void
    {
        $recipes = $this->create(Recipe::class, 2);

        Livewire::test(ListRecipes::class)
            ->searchTable((string) $recipes->last()->id)
            ->assertCanSeeTableRecords([$recipes->last()])
            ->assertCanNotSeeTableRecords([$recipes->first()]);
    }

    #[Test]
    #[DataProvider('dateColumns')]
    public function itShowsTheDateColumnsAsDateTimes(string $column): void
    {
        Livewire::test(ListRecipes::class)
            ->assertTableColumnExists($column, fn (TextColumn $c): bool => $c->isDateTime());
    }

    public static function dateColumns(): array
    {
        return [
            'created at' => ['created_at'],
            'publish at' => ['publish_at'],
        ];
    }

    #[Test]
    public function itLinksEachRowToTheEditPage(): void
    {
        $recipe = $this->create(Recipe::class);

        $this->assertSame(
            RecipeResource::getUrl('edit', ['record' => $recipe]),
            Livewire::test(ListRecipes::class)->instance()->getTable()->getRecordUrl($recipe)
        );
    }

    #[Test]
    public function itOffersAViewLinkForALiveRecipe(): void
    {
        $recipe = $this->create(Recipe::class, ['live' => true]);

        Livewire::test(ListRecipes::class)
            ->assertActionVisible(TestAction::make('view')->table($recipe));
    }

    #[Test]
    public function itHidesTheViewLinkForARecipeThatIsntLive(): void
    {
        $recipe = $this->build(Recipe::class)->notLive()->create();

        Livewire::test(ListRecipes::class)
            ->assertActionHidden(TestAction::make('view')->table($recipe));
    }

    #[Test]
    public function theViewLinkOpensTheRecipeOnTheWebsiteInANewTab(): void
    {
        $recipe = $this->create(Recipe::class, ['live' => true]);

        Livewire::test(ListRecipes::class)->assertActionExists(
            TestAction::make('view')->table($recipe),
            fn (Action $action): bool => $action->getUrl() === $recipe->absolute_link && $action->shouldOpenUrlInNewTab(),
        );
    }

    #[Test]
    public function itOffersAMetricsLinkForEveryRecipe(): void
    {
        $recipe = $this->build(Recipe::class)->notLive()->create();

        Livewire::test(ListRecipes::class)->assertActionExists(
            TestAction::make('metrics')->table($recipe),
            fn (Action $action): bool => $action->getUrl() === RecipeResource::getUrl('metrics', ['record' => $recipe]),
        );
    }

    #[Test]
    public function itOffersAnEditActionForEveryRecipe(): void
    {
        $recipe = $this->create(Recipe::class);

        Livewire::test(ListRecipes::class)->assertActionExists(TestAction::make(EditAction::class)->table($recipe));
    }
}
