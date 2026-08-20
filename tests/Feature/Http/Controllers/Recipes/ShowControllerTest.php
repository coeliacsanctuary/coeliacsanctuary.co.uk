<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Recipes;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Comments\GetCommentsForItemAction;
use App\Models\Recipes\Recipe;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withRecipes(1);

        $this->recipe = Recipe::query()->first();
    }

    #[Test]
    public function itReturnsNotFoundForARecipeDoesntExist(): void
    {
        $this->get(route('recipe.show', ['recipe' => 'foobar']))->assertNotFound();
    }

    protected function visitRecipe(): TestResponse
    {
        return $this->get(route('recipe.show', ['recipe' => $this->recipe]));
    }

    #[Test]
    public function itReturnsNotFoundForARecipeThatIsntLive(): void
    {
        $this->recipe->update(['live' => false]);

        $this->visitRecipe()->assertNotFound();
    }

    #[Test]
    public function itReturnsOkForARecipeThatIsLive(): void
    {
        $this->visitRecipe()->assertOk();
    }

    #[Test]
    public function itCallsTheGetCommentsForItemAction(): void
    {
        $this->expectAction(GetCommentsForItemAction::class, [Recipe::class]);

        $this->visitRecipe();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitRecipe()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Recipe/Show')
                    ->has('recipe')
                    ->where('recipe.title', 'Recipe 0')
                    ->etc()
            );
    }

    #[Test]
    public function itSetsTheMediavineBodyClass(): void
    {
        $this->visitRecipe()
            ->assertInertia(fn (Assert $page) => $page->where('meta.bodyClass', 'mv-recipe')->etc())
            ->assertSee('<body class="mb-0 mv-recipe">', false);
    }
}
