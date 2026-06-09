<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Recipes\Feed;

use App\Feeds\RecipeFeed;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withRecipes(12);
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->get(route('recipe.feed'))->assertOk();
    }

    #[Test]
    public function itCallsTheBlogFeedClass(): void
    {
        $this->mock(RecipeFeed::class)
            ->shouldReceive('render')
            ->andReturn(view('static.feed'))
            ->once();

        $this->get(route('recipe.feed'));
    }

    #[Test]
    public function itReturnsAnXmlHeader(): void
    {
        $this->get(route('recipe.feed'))->assertHeader('Content-Type', 'text/xml; charset=utf-8');
    }

    #[Test]
    public function itReturnsTheFeedView(): void
    {
        $this->get(route('recipe.feed'))->assertViewIs('static.feed');
    }
}
