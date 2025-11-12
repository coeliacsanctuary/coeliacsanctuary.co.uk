<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\Recipes;

use App\Models\Recipes\Recipe;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withRecipes(15);
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.recipes.index'))->assertForbidden();
    }

    #[Test]
    public function itReturnsADataProperty(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => []]);
    }

    #[Test]
    public function itReturnsEachItemInTheExpectedFormat(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'title',
                        'link',
                        'image',
                        'date',
                        'description',
                    ],
                ],
            ]);
    }

    #[Test]
    public function itReturns12Items(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonCount(12, 'data');
    }

    #[Test]
    public function itDoesntReturnTheOldestRecipe(): void
    {
        $recipe = Recipe::query()->oldest()->first();

        $request = $this->makeRequest();

        $recipes = $request->collect('data');

        $this->assertNotContains($recipe->title, $recipes->pluck('title'));
    }

    #[Test]
    public function itReturnsTheNewestRecipeFirst(): void
    {
        $recipe = Recipe::query()->latest()->first();

        $this->makeRequest()
            ->assertOk()
            ->assertJsonPath('data.0.title', $recipe->title);
    }

    protected function makeRequest(string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.recipes.index'),
            ['x-coeliac-source' => $source],
        );
    }
}
