<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Recipes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\Recipes\GetRecipeFiltersForIndexAction;
use App\Contracts\Recipes\FilterableRecipeRelation;
use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeMeal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GetRecipeFiltersForIndexActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');
    }

    public static function filterableImplementations(): array
    {
        return [
            'recipe features' => [RecipeFeature::class, 'features'],
            'recipe meals' => [RecipeMeal::class, 'meals'],
            'recipe free from' => [RecipeAllergen::class, 'allergens'],
        ];
    }

    /** @param  class-string<FilterableRecipeRelation>  $relationship */
    #[Test]
    #[DataProvider('filterableImplementations')]
    public function itReturnsTheFilterableRelationshipWithNoFiltersInTheRequest(string $relationship, string $name): void
    {
        $this->build(Recipe::class)
            ->has($this->build($relationship)->count(5), $name)
            ->count(5)
            ->create();

        $this->assertCount(25, $this->callAction(GetRecipeFiltersForIndexAction::class, $relationship));
    }

    /** @param  class-string<FilterableRecipeRelation>  $relationship */
    #[Test]
    #[DataProvider('filterableImplementations')]
    public function itReturnsTheFilterableInstanceThatHasAnAdditionalFeatureFilter(string $relationship, string $name): void
    {
        $this->build(Recipe::class)
            ->has($this->build($relationship)->count(5), $name)
            ->count(5)
            ->create();

        $feature = $this->create(RecipeFeature::class, ['slug' => 'feature']);
        $meal = $this->create(RecipeMeal::class, ['slug' => 'meal']);
        $allergen = $this->create(RecipeAllergen::class, ['slug' => 'allergen']);

        /** @var Recipe $recipe */
        $recipe = $this->create(Recipe::class);

        $recipe->features()->attach($feature);
        $recipe->meals()->attach($meal);
        $recipe->allergens()->attach($allergen);

        /** @var Collection $features */
        $features = $this->callAction(GetRecipeFiltersForIndexAction::class, $relationship, ['features' => ['feature']]);

        $this->assertCount(1, $features);
        $this->assertEquals(1, data_get($features, '0.recipes_count'));
    }

    /** @param  class-string<FilterableRecipeRelation>  $relationship */
    #[Test]
    #[DataProvider('filterableImplementations')]
    public function itReturnsTheFilterableInstanceThatHasAnAdditionalMealFilter(string $relationship, string $name): void
    {
        $this->build(Recipe::class)
            ->has($this->build($relationship)->count(5), $name)
            ->count(5)
            ->create();

        $feature = $this->create(RecipeFeature::class, ['slug' => 'feature']);
        $meal = $this->create(RecipeMeal::class, ['slug' => 'meal']);
        $allergen = $this->create(RecipeAllergen::class, ['slug' => 'allergen']);

        /** @var Recipe $recipe */
        $recipe = $this->create(Recipe::class);

        $recipe->features()->attach($feature);
        $recipe->meals()->attach($meal);
        $recipe->allergens()->attach($allergen);

        /** @var Collection $meals */
        $meals = $this->callAction(GetRecipeFiltersForIndexAction::class, $relationship, ['meals' => ['meal']]);

        $this->assertCount(1, $meals);
        $this->assertEquals(1, data_get($meals, '0.recipes_count'));
    }

    /** @param  class-string<FilterableRecipeRelation>  $relationship */
    #[Test]
    #[DataProvider('filterableImplementations')]
    public function itReturnsTheFilterableInstanceThatHasAnAdditionalFreeFromFilter(string $relationship, string $name): void
    {
        $this->build(Recipe::class)
            ->has($this->build($relationship)->count(5), $name)
            ->count(5)
            ->create();

        $feature = $this->create(RecipeFeature::class, ['slug' => 'feature']);
        $meal = $this->create(RecipeMeal::class, ['slug' => 'meal']);
        $allergen = $this->create(RecipeAllergen::class, ['slug' => 'allergen']);

        /** @var Recipe $recipe */
        $recipe = $this->create(Recipe::class);

        $recipe->features()->attach($feature);
        $recipe->meals()->attach($meal);
        $recipe->allergens()->attach($allergen);

        /** @var Collection $freeFroms */
        $freeFroms = $this->callAction(GetRecipeFiltersForIndexAction::class, $relationship, ['freeFrom' => ['allergen']]);

        $this->assertCount(1, $freeFroms);
        $this->assertEquals(1, data_get($freeFroms, '0.recipes_count'));
    }

    #[Test]
    public function itDoesntReturnTheGlutenAllergenAsAFreeFromOption(): void
    {
        $this->create(RecipeAllergen::class, ['allergen' => 'Gluten', 'slug' => 'gluten']);
        $this->create(RecipeAllergen::class, ['allergen' => 'Dairy', 'slug' => 'dairy']);

        $allergens = $this->callAction(GetRecipeFiltersForIndexAction::class, RecipeAllergen::class);

        $this->assertCount(1, $allergens);
        $this->assertSame('Dairy', $allergens->first()['allergen']);
    }
}
