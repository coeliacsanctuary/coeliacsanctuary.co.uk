<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Forms\Components;

use App\Filament\Forms\Components\RelatedRecipesSearch;
use App\Models\Recipes\Recipe;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\BuildsFilamentSchemas;
use Tests\TestCase;

class RelatedRecipesSearchTest extends TestCase
{
    use BuildsFilamentSchemas;

    protected Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->recipe = $this->create(Recipe::class, ['title' => 'Gluten Free Victoria Sponge']);
    }

    #[Test]
    public function itFillsItsStateFromTheRelatedRecipesInTheirStoredOrder(): void
    {
        $first = $this->create(Recipe::class);
        $second = $this->create(Recipe::class);

        $this->recipe->relatedRecipes()->attach([
            $second->id => ['position' => 0],
            $first->id => ['position' => 1],
        ]);

        $this->assertSame([$second->id, $first->id], $this->field()->getState());
    }

    #[Test]
    public function itStartsEmptyForARecipeWithNoRelatedRecipes(): void
    {
        $this->assertSame([], $this->field()->getState());
    }

    #[Test]
    public function itDescribesTheSelectedRecipesForTheView(): void
    {
        $related = $this->create(Recipe::class, ['title' => 'Gluten Free Scones']);

        $this->recipe->relatedRecipes()->attach($related->id);

        $this->assertSame(
            [['id' => $related->id, 'title' => 'Gluten Free Scones', 'image' => null]],
            $this->field()->getSelectedRecipes(),
        );
    }

    #[Test]
    public function itDescribesTheSelectedRecipesInTheirStoredOrder(): void
    {
        $first = $this->create(Recipe::class, ['title' => 'Gluten Free Scones']);
        $second = $this->create(Recipe::class, ['title' => 'Gluten Free Flapjacks']);

        $this->recipe->relatedRecipes()->attach([
            $second->id => ['position' => 0],
            $first->id => ['position' => 1],
        ]);

        $this->assertSame(
            ['Gluten Free Flapjacks', 'Gluten Free Scones'],
            collect($this->field()->getSelectedRecipes())->pluck('title')->all(),
        );
    }

    #[Test]
    public function itAttachesTheRecipesInItsStateWhenSaving(): void
    {
        $related = $this->create(Recipe::class, 2);

        $this->saveWithState($related->pluck('id')->all());

        $this->assertSame($related->pluck('id')->all(), $this->relatedIds());
    }

    #[Test]
    public function itDetachesARecipeRemovedFromItsState(): void
    {
        $kept = $this->create(Recipe::class);
        $dropped = $this->create(Recipe::class);

        $this->recipe->relatedRecipes()->attach([$kept->id, $dropped->id]);

        $this->saveWithState([$kept->id]);

        $this->assertSame([$kept->id], $this->relatedIds());
    }

    #[Test]
    public function itDetachesEveryRecipeWhenTheStateIsEmptied(): void
    {
        $this->recipe->relatedRecipes()->attach($this->create(Recipe::class, 2)->pluck('id'));

        $this->saveWithState([]);

        $this->assertSame([], $this->relatedIds());
    }

    #[Test]
    public function itStoresThePositionOfEachRecipeWhenSaving(): void
    {
        $first = $this->create(Recipe::class);
        $second = $this->create(Recipe::class);

        $this->saveWithState([$second->id, $first->id]);

        $this->assertSame(
            [$second->id => 0, $first->id => 1],
            $this->recipe->relatedRecipes()->get()
                ->mapWithKeys(fn (Recipe $recipe): array => [$recipe->id => $recipe->pivot->position])
                ->all(),
        );
    }

    #[Test]
    public function itRewritesThePositionsWhenTheOrderChanges(): void
    {
        $first = $this->create(Recipe::class);
        $second = $this->create(Recipe::class);

        $this->saveWithState([$first->id, $second->id]);
        $this->saveWithState([$second->id, $first->id]);

        $this->assertSame([$second->id, $first->id], $this->orderedRelatedIds());
    }

    #[Test]
    public function itPositionsARecipeAddedToTheEnd(): void
    {
        $first = $this->create(Recipe::class);
        $added = $this->create(Recipe::class);

        $this->saveWithState([$first->id]);
        $this->saveWithState([$first->id, $added->id]);

        $this->assertSame([$first->id, $added->id], $this->orderedRelatedIds());
    }

    #[Test]
    public function itFindsRecipesByPartOfTheirTitle(): void
    {
        $scones = $this->create(Recipe::class, ['title' => 'Gluten Free Scones']);
        $this->create(Recipe::class, ['title' => 'Gluten Free Flapjacks']);

        $this->assertSame([$scones->id], collect($this->field()->searchRecipes('Scon'))->pluck('id')->all());
    }

    #[Test]
    public function itIgnoresCaseWhenSearching(): void
    {
        $scones = $this->create(Recipe::class, ['title' => 'Gluten Free Scones']);

        $this->assertSame([$scones->id], collect($this->field()->searchRecipes('scones'))->pluck('id')->all());
    }

    #[Test]
    public function itReturnsNothingForAnEmptySearch(): void
    {
        $this->create(Recipe::class, ['title' => 'Gluten Free Scones']);

        $this->assertSame([], $this->field()->searchRecipes(''));
        $this->assertSame([], $this->field()->searchRecipes('   '));
    }

    #[Test]
    public function itReturnsAtMostTenResults(): void
    {
        $this->create(Recipe::class, 12, ['title' => 'Gluten Free Scones']);

        $this->assertCount(10, $this->field()->searchRecipes('Scones'));
    }

    #[Test]
    public function itNeverOffersTheRecipeBeingEdited(): void
    {
        $this->recipe->update(['title' => 'Gluten Free Scones']);

        $other = $this->create(Recipe::class, ['title' => 'Gluten Free Scones Two']);

        $this->assertSame([$other->id], collect($this->field()->searchRecipes('Scones'))->pluck('id')->all());
    }

    #[Test]
    public function itNeverOffersARecipeThatIsAlreadySelected(): void
    {
        $selected = $this->create(Recipe::class, ['title' => 'Gluten Free Scones']);
        $available = $this->create(Recipe::class, ['title' => 'Gluten Free Scones Two']);

        $this->recipe->relatedRecipes()->attach($selected->id);

        $this->assertSame([$available->id], collect($this->field()->searchRecipes('Scones'))->pluck('id')->all());
    }

    #[Test]
    public function itOffersRecipesThatArentLive(): void
    {
        $draft = $this->build(Recipe::class)->notLive()->create(['title' => 'Gluten Free Scones']);

        $this->assertSame([$draft->id], collect($this->field()->searchRecipes('Scones'))->pluck('id')->all());
    }

    #[Test]
    public function itUsesTheHeaderImageForAResult(): void
    {
        $related = $this->create(Recipe::class, ['title' => 'Gluten Free Scones']);
        $related->addMedia(UploadedFile::fake()->image('header.jpg'))->toMediaCollection('primary');

        $this->assertStringContainsString('header', $this->field()->searchRecipes('Scones')[0]['image']);
    }

    #[Test]
    public function itFallsBackToTheSquareImageForAResult(): void
    {
        $related = $this->create(Recipe::class, ['title' => 'Gluten Free Scones']);
        $related->addMedia(UploadedFile::fake()->image('square.jpg'))->toMediaCollection('square');

        $this->assertStringContainsString('square', $this->field()->searchRecipes('Scones')[0]['image']);
    }

    #[Test]
    public function itHasNoImageForARecipeWithoutOne(): void
    {
        $this->create(Recipe::class, ['title' => 'Gluten Free Scones']);

        $this->assertNull($this->field()->searchRecipes('Scones')[0]['image']);
    }

    protected function field(): RelatedRecipesSearch
    {
        return $this->mountedComponent(
            'relatedRecipes',
            [RelatedRecipesSearch::make('relatedRecipes')],
            'edit',
            $this->recipe,
        );
    }

    protected function saveWithState(array $state): void
    {
        $field = $this->field();

        $field->state($state);
        $field->saveRelationships();
    }

    /** @return array<int, int> */
    protected function orderedRelatedIds(): array
    {
        return $this->recipe->relatedRecipes()->pluck('recipes.id')->all();
    }

    /** @return array<int, int> */
    protected function relatedIds(): array
    {
        return $this->recipe->relatedRecipes()->pluck('recipes.id')->sort()->values()->all();
    }
}
