<?php

declare(strict_types=1);

namespace Tests\Unit\Resources\Recipes;

use App\Models\Faqs\Faq;
use App\Models\Recipes\Recipe;
use App\Resources\Recipes\RecipeShowResource;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipeShowResourceTest extends TestCase
{
    protected Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withRecipes(1);

        $this->recipe = Recipe::query()->first();
    }

    /** @return array<int, array{heading: string|null, items: array<int, string>}> */
    protected function ingredientsFor(string $ingredients): array
    {
        $this->recipe->update(['ingredients' => $ingredients]);

        return (new RecipeShowResource($this->recipe->fresh()))->toArray(new Request())['ingredients'];
    }

    #[Test]
    public function itReturnsTheRelatedRecipesInTheirStoredOrder(): void
    {
        $first = $this->create(Recipe::class, ['title' => 'Gluten Free Scones']);
        $second = $this->create(Recipe::class, ['title' => 'Gluten Free Flapjacks']);

        $this->recipe->relatedRecipes()->attach([
            $second->id => ['position' => 0],
            $first->id => ['position' => 1],
        ]);

        $related = (new RecipeShowResource($this->recipe->fresh()))->toArray(new Request())['related_recipes'];

        $this->assertSame(
            ['Gluten Free Flapjacks', 'Gluten Free Scones'],
            collect($related)->map(fn ($recipe) => $recipe->resource->title)->all(),
        );
    }

    #[Test]
    public function itGroupsUngroupedIngredientsUnderNoHeading(): void
    {
        $ingredients = $this->ingredientsFor("400g white chocolate\n25g butter\n1tsp peppermint extract");

        $this->assertCount(1, $ingredients);
        $this->assertNull($ingredients[0]['heading']);
        $this->assertSame(
            ['400g white chocolate', '25g butter', '1tsp peppermint extract'],
            $ingredients[0]['items'],
        );
    }

    #[Test]
    public function itSplitsIngredientsIntoSectionsOnAStrongHeading(): void
    {
        $ingredients = $this->ingredientsFor("<strong>Cake</strong>\n250g runny honey\n3 large eggs\n<strong>Icing</strong>\n40g icing sugar");

        $this->assertCount(2, $ingredients);

        $this->assertSame('Cake', $ingredients[0]['heading']);
        $this->assertSame(['250g runny honey', '3 large eggs'], $ingredients[0]['items']);

        $this->assertSame('Icing', $ingredients[1]['heading']);
        $this->assertSame(['40g icing sugar'], $ingredients[1]['items']);
    }

    #[Test]
    public function itKeepsIngredientsListedBeforeTheFirstHeadingInTheirOwnGroup(): void
    {
        $ingredients = $this->ingredientsFor("340g plain flour\n<strong>Topping</strong>\n200g marzipan");

        $this->assertCount(2, $ingredients);

        $this->assertNull($ingredients[0]['heading']);
        $this->assertSame(['340g plain flour'], $ingredients[0]['items']);

        $this->assertSame('Topping', $ingredients[1]['heading']);
        $this->assertSame(['200g marzipan'], $ingredients[1]['items']);
    }

    #[Test]
    public function itReadsAHeadingThatHasBeenClosedTwice(): void
    {
        $ingredients = $this->ingredientsFor("50g unsalted butter\n</strong>Topping</strong>\n200g milk chocolate");

        $this->assertCount(2, $ingredients);
        $this->assertSame('Topping', $ingredients[1]['heading']);
        $this->assertSame(['200g milk chocolate'], $ingredients[1]['items']);
    }

    #[Test]
    public function itDropsBlankLinesBetweenIngredients(): void
    {
        $ingredients = $this->ingredientsFor("400g white chocolate\n\n\n25g butter\n   \n397g condensed milk");

        $this->assertCount(1, $ingredients);
        $this->assertSame(
            ['400g white chocolate', '25g butter', '397g condensed milk'],
            $ingredients[0]['items'],
        );
    }

    #[Test]
    public function itLeavesInlineMarkupOnAnIngredientIntact(): void
    {
        $ingredients = $this->ingredientsFor('350g <a href="http://www.angelsandcookies.co.uk/">Angels and Cookies</a> Chocolate Chip Cookie Dough');

        $this->assertSame(
            ['350g <a href="http://www.angelsandcookies.co.uk/">Angels and Cookies</a> Chocolate Chip Cookie Dough'],
            $ingredients[0]['items'],
        );
    }

    #[Test]
    public function itTreatsAPartiallyBoldLineAsAnIngredientRatherThanAHeading(): void
    {
        $ingredients = $this->ingredientsFor('<strong>Layer 1 - Sponge</strong> (or you could use shop bought)');

        $this->assertCount(1, $ingredients);
        $this->assertNull($ingredients[0]['heading']);
        $this->assertSame(
            ['<strong>Layer 1 - Sponge</strong> (or you could use shop bought)'],
            $ingredients[0]['items'],
        );
    }

    #[Test]
    public function itReturnsNullFaqsWhenThereAreNoFaqs(): void
    {
        $resource = (new RecipeShowResource($this->recipe))->toArray(new Request());

        $this->assertNull($resource['faqs']);
    }

    #[Test]
    public function itReturnsFaqsFromTheRelation(): void
    {
        $this->build(Faq::class)->on($this->recipe)->create(['question' => 'Is this gluten free?', 'answer' => 'Yes!']);
        $this->build(Faq::class)->on($this->recipe)->create(['question' => 'Can I freeze it?', 'answer' => 'Absolutely.']);

        $resource = (new RecipeShowResource($this->recipe->fresh()))->toArray(new Request());

        $this->assertCount(2, $resource['faqs']);
        $this->assertSame('Is this gluten free?', $resource['faqs'][0]['question']);
        $this->assertSame('Yes!', $resource['faqs'][0]['answer']);
        $this->assertSame('Can I freeze it?', $resource['faqs'][1]['question']);
        $this->assertSame('Absolutely.', $resource['faqs'][1]['answer']);
    }
}
