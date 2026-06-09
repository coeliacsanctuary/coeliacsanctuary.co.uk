<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Recipes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Actions\Recipes\GetRecipeFiltersForIndexAction;
use App\Actions\Recipes\GetRecipesForIndexAction;
use App\Contracts\Recipes\FilterableRecipeRelation;
use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeMeal;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withRecipes(30);
    }

    #[Test]
    public function itLoadsTheRecipeListPage(): void
    {
        $this->get(route('recipe.index'))->assertOk();
    }

    #[Test]
    public function itCallsTheGetRecipesForIndexAction(): void
    {
        $this->expectAction(GetRecipesForIndexAction::class);

        $this->get(route('recipe.index'));

        $this->expectAction(GetRecipesForIndexAction::class, [function ($filters): bool {
            $this->assertArrayHasKey('features', $filters);
            $this->assertArrayHasKey('meals', $filters);
            $this->assertArrayHasKey('freeFrom', $filters);

            $this->assertContains('test', $filters['meals']);

            return true;
        }]);

        $this->get(route('recipe.index', ['meals' => 'test']));
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageForRouteAction(): void
    {
        $this->expectAction(GetOpenGraphImageForRouteAction::class, ['recipe']);

        $this->get(route('recipe.index'));
    }

    public static function filterableImplementations(): array
    {
        return [
            'recipe features' => [RecipeFeature::class, 'features'],
            'recipe meals' => [RecipeMeal::class, 'meals'],
            'recipe free from' => [RecipeAllergen::class, 'freeFrom'],
        ];
    }

    /** @param  class-string<FilterableRecipeRelation>  $relationship */
    #[Test]
    #[DataProvider('filterableImplementations')]
    public function itCallsTheGetRecipeFiltersForIndexAction(string $relationship, string $name): void
    {
        $this->expectAction(
            GetRecipeFiltersForIndexAction::class,
            [function ($class, $filters) use ($name): bool {
                $this->assertContains($class, [RecipeFeature::class, RecipeMeal::class, RecipeAllergen::class]);
                $this->assertArrayHasKey('features', $filters);
                $this->assertArrayHasKey('meals', $filters);
                $this->assertArrayHasKey('freeFrom', $filters);

                $this->assertContains('test', $filters[$name]);

                return true;
            }],
            false,
        );

        $this->get(route('recipe.index', [$name => 'test']));
    }

    #[Test]
    public function itReturnsTheFirst12Recipes(): void
    {
        $this->get(route('recipe.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Recipe/Index')
                    ->has('recipes')
                    ->has(
                        'recipes.data',
                        12,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'description', 'date', 'image', 'header_image_alt_text', 'square_image', 'link', 'description', 'features', 'nutrition'])
                    )
                    ->where('recipes.data.0.title', 'Recipe 0')
                    ->where('recipes.data.1.title', 'Recipe 1')
                    ->has('recipes.links')
                    ->has('recipes.meta')
                    ->where('recipes.meta.current_page', 1)
                    ->where('recipes.meta.per_page', 12)
                    ->where('recipes.meta.total', 30)
                    ->etc()
            );
    }

    #[Test]
    public function itCanLoadOtherPages(): void
    {
        $this->get(route('recipe.index', ['page' => 2]))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Recipe/Index')
                    ->has('recipes')
                    ->has(
                        'recipes.data',
                        12,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'description', 'date', 'image', 'header_image_alt_text', 'square_image', 'link', 'description', 'features', 'nutrition'])
                    )
                    ->where('recipes.data.0.title', 'Recipe 12')
                    ->where('recipes.data.1.title', 'Recipe 13')
                    ->has('recipes.links')
                    ->has('recipes.meta')
                    ->where('recipes.meta.current_page', 2)
                    ->where('recipes.meta.per_page', 12)
                    ->where('recipes.meta.total', 30)
                    ->etc()
            );
    }
}
