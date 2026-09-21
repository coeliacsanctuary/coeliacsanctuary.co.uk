<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Recipes;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Recipes\Recipe;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    #[Test]
    public function itErrorsIfARecipeCantBeFound(): void
    {
        $this->get(route('api.recipes.show', 'foo'))->assertNotFound();
    }

    #[Test]
    public function itErrorsIfARecipeIsntLive(): void
    {
        $recipe = $this->build(Recipe::class)->notLive()->create();

        $this->get(route('api.recipes.show', $recipe))->assertNotFound();
    }

    #[Test]
    public function itErrorsIfARecipeIsDraft(): void
    {
        $recipe = $this->build(Recipe::class)->draft()->create();

        $this->get(route('api.recipes.show', $recipe))->assertNotFound();
    }

    #[Test]
    public function itReturnsTheRecipe(): void
    {
        $this->withRecipes(1);

        $recipe = Recipe::query()->first();

        $this->get(route('api.recipes.show', $recipe))->assertOk();
    }
}
