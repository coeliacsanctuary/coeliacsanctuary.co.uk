<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\State\ChatContext;
use App\Ai\Tools\ViewRecipeTool;
use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeNutrition;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewRecipeToolTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }

    #[Test]
    public function itReturnsTheRecipeData(): void
    {
        $recipe = $this->create(Recipe::class, [
            'title' => 'GF Chocolate Cake',
            'slug' => 'gf-chocolate-cake',
            'description' => 'A delicious cake',
            'ingredients' => 'Flour, Sugar, Eggs',
            'method' => 'Mix and bake',
            'prep_time' => '30 Minutes',
            'cook_time' => '45 Minutes',
        ]);

        $this->create(RecipeNutrition::class, [
            'recipe_id' => $recipe->id,
            'calories' => 250,
            'carbs' => 30,
            'fibre' => 2,
            'fat' => 12,
            'sugar' => 18,
            'protein' => 5,
        ]);

        $tool = new ViewRecipeTool();
        $result = json_decode((string) $tool->handle(new Request(['recipe_slug' => 'gf-chocolate-cake'])), true);

        $this->assertEquals('GF Chocolate Cake', $result['title']);
        $this->assertEquals('A delicious cake', $result['description']);
        $this->assertEquals('Flour, Sugar, Eggs', $result['ingredients']);
        $this->assertEquals('Mix and bake', $result['method']);
        $this->assertArrayHasKey('link', $result);
        $this->assertEquals('30 Minutes', $result['timing']['prep_time']);
        $this->assertEquals('45 Minutes', $result['timing']['cook_time']);
        $this->assertEquals(250, $result['nutrition']['calories']);
        $this->assertEquals(30, $result['nutrition']['carbs']);
        $this->assertEquals(2, $result['nutrition']['fibre']);
        $this->assertEquals(12, $result['nutrition']['fat']);
        $this->assertEquals(18, $result['nutrition']['sugar']);
        $this->assertEquals(5, $result['nutrition']['protein']);
    }

    #[Test]
    public function itIncludesRecipeFeatures(): void
    {
        $recipe = $this->create(Recipe::class, ['slug' => 'test-recipe']);
        $this->create(RecipeNutrition::class, ['recipe_id' => $recipe->id]);

        $feature = $this->create(RecipeFeature::class, ['feature' => 'Low Calorie']);
        $recipe->features()->attach($feature);

        $tool = new ViewRecipeTool();
        $result = json_decode((string) $tool->handle(new Request(['recipe_slug' => 'test-recipe'])), true);

        $this->assertContains('Low Calorie', $result['features']);
    }

    #[Test]
    public function itIncludesContainsAllergens(): void
    {
        $recipe = $this->create(Recipe::class, ['slug' => 'test-recipe']);
        $this->create(RecipeNutrition::class, ['recipe_id' => $recipe->id]);

        $allergenA = $this->create(RecipeAllergen::class, ['allergen' => 'Dairy']);
        $allergenB = $this->create(RecipeAllergen::class, ['allergen' => 'Eggs']);

        // Attach only one allergen as "free from" - the other should show as "contains"
        $recipe->allergens()->attach($allergenA);

        $tool = new ViewRecipeTool();
        $result = json_decode((string) $tool->handle(new Request(['recipe_slug' => 'test-recipe'])), true);

        // The "contains" list should include allergens NOT attached to the recipe (i.e., not free from)
        $this->assertContains('Eggs', $result['contains']);
        $this->assertNotContains('Dairy', $result['contains']);
    }

    #[Test]
    public function itThrowsModelNotFoundForInvalidSlug(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $tool = new ViewRecipeTool();
        $tool->handle(new Request(['recipe_slug' => 'non-existent-recipe']));
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $recipe = $this->create(Recipe::class, ['slug' => 'test-recipe']);
        $this->create(RecipeNutrition::class, ['recipe_id' => $recipe->id]);

        $tool = new ViewRecipeTool();
        $tool->handle(new Request(['recipe_slug' => 'test-recipe']));

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('ViewRecipeTool', $toolUses->first()['tool']);
    }
}
