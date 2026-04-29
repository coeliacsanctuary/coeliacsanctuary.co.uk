<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\Collections\Feed;

use App\Feeds\EateryCollectionFeed;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('feature disabled');

        $this->withEateryCollections(12);
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->get(route('eating-out.collections.feed'))->assertOk();
    }

    #[Test]
    public function itCallsTheBlogFeedClass(): void
    {
        $this->mock(EateryCollectionFeed::class)
            ->shouldReceive('render')
            ->andReturn(view('static.feed'))
            ->once();

        $this->get(route('eating-out.collections.feed'));
    }

    #[Test]
    public function itReturnsAnXmlHeader(): void
    {
        $this->get(route('eating-out.collections.feed'))->assertHeader('Content-Type', 'text/xml; charset=utf-8');
    }

    #[Test]
    public function itReturnsTheFeedView(): void
    {
        $this->get(route('eating-out.collections.feed'))->assertViewIs('static.feed');
    }
}
