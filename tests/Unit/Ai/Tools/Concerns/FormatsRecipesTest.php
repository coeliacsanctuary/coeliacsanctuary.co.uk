<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\Concerns;

use App\Ai\Concerns\FormatsRecipes;
use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeNutrition;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FormatsRecipesTest extends TestCase
{
    #[Test]
    public function itFormatsARecipeCorrectly(): void
    {
        $recipe = $this->create(Recipe::class, [
            'title' => 'Test Cake',
            'meta_description' => 'A delicious test cake',
            'serving_size' => '8 Slices',
            'per' => 'slice',
        ]);

        $recipe->addMedia(UploadedFile::fake()->image('recipe.jpg'))->toMediaCollection('primary');

        $this->create(RecipeNutrition::class, [
            'recipe_id' => $recipe->id,
            'calories' => 300,
            'carbs' => 40,
            'fibre' => 2,
            'fat' => 15,
            'sugar' => 20,
            'protein' => 5,
        ]);

        $recipe->load(['nutrition', 'media']);

        $formatter = new class () {
            use FormatsRecipes;

            public function format(Recipe $recipe): array
            {
                return $this->formatRecipe($recipe);
            }
        };

        $result = $formatter->format($recipe);

        $this->assertEquals($recipe->id, $result['id']);
        $this->assertEquals('Test Cake', $result['title']);
        $this->assertEquals('A delicious test cake', $result['description']);
        $this->assertArrayHasKey('link', $result);
        $this->assertArrayHasKey('image', $result);
        $this->assertArrayHasKey('nutritional_info', $result);
        $this->assertEquals('8 Slices', $result['nutritional_info']['servings']);
        $this->assertEquals('slice', $result['nutritional_info']['portion_size']);
        $this->assertEquals(300, $result['nutritional_info']['calories']);
        $this->assertEquals(40, $result['nutritional_info']['carbs']);
        $this->assertEquals(2, $result['nutritional_info']['fibre']);
        $this->assertEquals(15, $result['nutritional_info']['fat']);
        $this->assertEquals(20, $result['nutritional_info']['sugar']);
        $this->assertEquals(5, $result['nutritional_info']['protein']);
    }
}
