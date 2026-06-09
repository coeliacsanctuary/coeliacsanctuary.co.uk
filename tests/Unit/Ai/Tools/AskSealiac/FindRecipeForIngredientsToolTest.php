<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\AskSealiac;

use App\Ai\State\ChatContext;
use App\Ai\Tools\AskSealiac\FindRecipeForIngredientsTool;
use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeNutrition;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FindRecipeForIngredientsToolTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');
    }

    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }

    protected function createRecipeWithMedia(array $attributes = []): Recipe
    {
        $recipe = $this->create(Recipe::class, $attributes);
        $recipe->addMedia(UploadedFile::fake()->image('recipe.jpg'))->toMediaCollection('primary');

        return $recipe;
    }

    #[Test]
    public function itReturnsRecipesMatchingIngredients(): void
    {
        $recipe = $this->createRecipeWithMedia([
            'title' => 'Chocolate Cake',
            'ingredients' => 'flour, sugar, cocoa, eggs, butter',
        ]);

        $this->create(RecipeNutrition::class, ['recipe_id' => $recipe->id]);

        $otherRecipe = $this->createRecipeWithMedia([
            'title' => 'Chicken Stir Fry',
            'ingredients' => 'chicken, rice, soy sauce, vegetables',
        ]);

        $this->create(RecipeNutrition::class, ['recipe_id' => $otherRecipe->id]);

        $tool = new FindRecipeForIngredientsTool();
        $result = json_decode((string) $tool->handle(new Request(['ingredients' => ['cocoa', 'flour']])), true);

        $this->assertCount(1, $result);
        $this->assertEquals('Chocolate Cake', $result[0]['title']);
    }

    #[Test]
    public function itReturnsMultipleRecipesMatchingDifferentIngredients(): void
    {
        $cakeRecipe = $this->createRecipeWithMedia([
            'title' => 'Chocolate Cake',
            'ingredients' => 'flour, sugar, cocoa, eggs',
        ]);

        $this->create(RecipeNutrition::class, ['recipe_id' => $cakeRecipe->id]);

        $breadRecipe = $this->createRecipeWithMedia([
            'title' => 'Banana Bread',
            'ingredients' => 'flour, sugar, bananas, eggs',
        ]);

        $this->create(RecipeNutrition::class, ['recipe_id' => $breadRecipe->id]);

        $tool = new FindRecipeForIngredientsTool();
        $result = json_decode((string) $tool->handle(new Request(['ingredients' => ['flour', 'eggs']])), true);

        $this->assertCount(2, $result);
    }

    #[Test]
    public function itReturnsEmptyWhenNoRecipesMatch(): void
    {
        $recipe = $this->createRecipeWithMedia(['ingredients' => 'flour, sugar, eggs']);
        $this->create(RecipeNutrition::class, ['recipe_id' => $recipe->id]);

        $tool = new FindRecipeForIngredientsTool();
        $result = json_decode((string) $tool->handle(new Request(['ingredients' => ['truffle', 'caviar']])), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itFormatsRecipeDataCorrectly(): void
    {
        $recipe = $this->createRecipeWithMedia([
            'title' => 'Test Recipe',
            'meta_description' => 'A test recipe',
            'ingredients' => 'test ingredient',
        ]);

        $this->create(RecipeNutrition::class, [
            'recipe_id' => $recipe->id,
            'calories' => 200,
            'carbs' => 25,
            'fibre' => 3,
            'fat' => 10,
            'sugar' => 15,
            'protein' => 8,
        ]);

        $tool = new FindRecipeForIngredientsTool();
        $result = json_decode((string) $tool->handle(new Request(['ingredients' => ['test ingredient']])), true);

        $this->assertCount(1, $result);
        $this->assertEquals('Test Recipe', $result[0]['title']);
        $this->assertEquals('A test recipe', $result[0]['description']);
        $this->assertArrayHasKey('link', $result[0]);
        $this->assertArrayHasKey('nutritional_info', $result[0]);
        $this->assertEquals(200, $result[0]['nutritional_info']['calories']);
        $this->assertEquals(25, $result[0]['nutritional_info']['carbs']);
        $this->assertEquals(3, $result[0]['nutritional_info']['fibre']);
        $this->assertEquals(10, $result[0]['nutritional_info']['fat']);
        $this->assertEquals(15, $result[0]['nutritional_info']['sugar']);
        $this->assertEquals(8, $result[0]['nutritional_info']['protein']);
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $tool = new FindRecipeForIngredientsTool();
        $tool->handle(new Request(['ingredients' => ['flour']]));

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('FindRecipeForIngredientsTool', $toolUses->first()['tool']);
    }
}
