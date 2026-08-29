<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Recipes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Actions\Recipes\BuildRecipeIndexDescriptionAction;
use App\Actions\Recipes\BuildRecipeIndexTitleAction;
use App\Actions\Recipes\GetRecipeFiltersForIndexAction;
use App\Actions\Recipes\GetRecipesForIndexAction;
use App\Contracts\Recipes\FilterableRecipeRelation;
use App\Models\Recipes\Recipe;
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

    #[Test]
    public function itReturnsTheFeaturesForEachRecipe(): void
    {
        $recipe = Recipe::query()->latest()->firstOrFail();

        $recipe->features()->sync([]);

        $recipe->features()->attach(
            $this->create(RecipeFeature::class, ['feature' => 'Vegan', 'slug' => 'vegan'])->id
        );

        $this->get(route('recipe.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('recipes.data.0.features', [['feature' => 'Vegan', 'slug' => 'vegan']])
                    ->etc()
            );
    }

    #[Test]
    public function itHasTheMetaDescription(): void
    {
        $this->get(route('recipe.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('meta.description', 'Gluten free recipes from Coeliac Sanctuary — tried and tested coeliac friendly bakes, dinners, breakfasts and puddings, all using simple supermarket ingredients.')
                    ->etc()
            );
    }

    #[Test]
    public function itCallsTheBuildRecipeIndexTitleAction(): void
    {
        $this->expectAction(BuildRecipeIndexTitleAction::class, [function ($filters): bool {
            $this->assertSame(['vegan'], $filters['features']);

            return true;
        }], return: 'Gluten Free Vegan Recipes');

        $this->get(route('recipe.index', ['features' => 'vegan']))
            ->assertInertia(fn (Assert $page) => $page->where('meta.title', 'Gluten Free Vegan Recipes')->etc());
    }

    #[Test]
    public function itCallsTheBuildRecipeIndexDescriptionAction(): void
    {
        $this->expectAction(BuildRecipeIndexDescriptionAction::class, [function ($filters): bool {
            $this->assertSame(['vegan'], $filters['features']);

            return true;
        }], return: 'A description of vegan recipes');

        $this->get(route('recipe.index', ['features' => 'vegan']))
            ->assertInertia(fn (Assert $page) => $page->where('meta.description', 'A description of vegan recipes')->etc());
    }

    #[Test]
    public function itDoesntIndexThePageWhenMoreThanOneFilterIsSelected(): void
    {
        $this->get(route('recipe.index', ['features' => 'vegan', 'meals' => 'breakfast']))
            ->assertInertia(fn (Assert $page) => $page->where('meta.doNotTrack', true)->etc());
    }

    #[Test]
    public function itIndexesThePageWhenASingleFilterIsSelected(): void
    {
        $this->get(route('recipe.index', ['features' => 'vegan']))
            ->assertInertia(fn (Assert $page) => $page->missing('meta.doNotTrack')->etc());
    }

    #[Test]
    public function itKeepsTheFilterAndPageInTheCanonicalUrl(): void
    {
        $this->get(route('recipe.index', ['features' => 'vegan', 'page' => 2]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('meta.currentUrl', route('recipe.index') . '?features=vegan&page=2')
                ->etc());
    }
}
