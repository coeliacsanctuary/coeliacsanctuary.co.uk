<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Feed;

use App\Feeds\CombinedFeed;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withBlogs(5);
        $this->withRecipes(5);
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->get(route('feed'))->assertOk();
    }

    #[Test]
    public function itCallsTheCombinedFeedClass(): void
    {
        $this->mock(CombinedFeed::class)
            ->shouldReceive('render')
            ->andReturn(view('static.feed'))
            ->once();

        $this->get(route('feed'));
    }

    #[Test]
    public function itReturnsAnXmlHeader(): void
    {
        $this->get(route('feed'))->assertHeader('Content-Type', 'text/xml; charset=utf-8');
    }

    #[Test]
    public function itReturnsTheFeedView(): void
    {
        $this->get(route('feed'))->assertViewIs('static.feed');
    }
}
