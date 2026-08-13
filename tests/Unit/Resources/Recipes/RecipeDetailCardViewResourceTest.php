<?php

declare(strict_types=1);

namespace Tests\Unit\Resources\Recipes;

use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeFeature;
use App\Resources\Recipes\RecipeDetailCardViewResource;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipeDetailCardViewResourceTest extends TestCase
{
    protected Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withRecipes(1);

        $this->recipe = Recipe::query()->first();
    }

    /** @return array<string, mixed> */
    protected function resource(): array
    {
        return (new RecipeDetailCardViewResource($this->recipe->fresh()))->toArray(new Request());
    }

    #[Test]
    public function itReturnsEachFeatureWithItsNameAndSlug(): void
    {
        $this->recipe->features()->sync([]);

        $this->recipe->features()->attach([
            $this->create(RecipeFeature::class, ['feature' => 'Vegan', 'slug' => 'vegan'])->id,
            $this->create(RecipeFeature::class, ['feature' => 'Low Sugar', 'slug' => 'low-sugar'])->id,
        ]);

        $features = $this->resource()['features'];

        $this->assertCount(2, $features);
        $this->assertSame(['feature' => 'Vegan', 'slug' => 'vegan'], $features[0]);
        $this->assertSame(['feature' => 'Low Sugar', 'slug' => 'low-sugar'], $features[1]);
    }

    #[Test]
    public function itReturnsAnEmptyArrayWhenTheRecipeHasNoFeatures(): void
    {
        $this->recipe->features()->sync([]);

        $this->assertSame([], $this->resource()['features']);
    }

    #[Test]
    public function itReturnsTheRecipeDetails(): void
    {
        $this->recipe->update([
            'title' => 'Gluten Free Brownies',
            'meta_description' => 'Rich, fudgy and completely gluten free.',
        ]);

        $resource = $this->resource();

        $this->assertSame('Gluten Free Brownies', $resource['title']);
        $this->assertSame('Rich, fudgy and completely gluten free.', $resource['description']);
        $this->assertSame($this->recipe->link, $resource['link']);
        $this->assertSame($this->recipe->published, $resource['date']);
    }

    #[Test]
    public function itReturnsTheNutritionDetails(): void
    {
        $this->recipe->nutrition->update(['calories' => 350]);

        $nutrition = $this->resource()['nutrition'];

        $this->assertSame(350, $nutrition['calories']);
        $this->assertSame($this->recipe->servings, $nutrition['servings']);
        $this->assertSame($this->recipe->portion_size, $nutrition['portion_size']);
    }
}
