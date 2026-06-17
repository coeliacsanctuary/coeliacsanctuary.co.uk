<?php

declare(strict_types=1);

namespace Tests\Unit\Resources\Recipes;

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

    #[Test]
    public function itReturnsNullFaqsWhenFaqsColumnIsNull(): void
    {
        $resource = (new RecipeShowResource($this->recipe))->toArray(new Request());

        $this->assertNull($resource['faqs']);
    }

    #[Test]
    public function itReturnsFaqsWhenSet(): void
    {
        $this->recipe->update([
            'faqs' => [
                ['question' => 'Is this gluten free?', 'answer' => 'Yes!'],
                ['question' => 'Can I freeze it?', 'answer' => 'Absolutely.'],
            ],
        ]);

        $resource = (new RecipeShowResource($this->recipe->fresh()))->toArray(new Request());

        $this->assertCount(2, $resource['faqs']);
        $this->assertSame('Is this gluten free?', $resource['faqs'][0]['question']);
        $this->assertSame('Yes!', $resource['faqs'][0]['answer']);
        $this->assertSame('Can I freeze it?', $resource['faqs'][1]['question']);
        $this->assertSame('Absolutely.', $resource['faqs'][1]['answer']);
    }
}
