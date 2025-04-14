<?php

declare(strict_types=1);

namespace Tests\Unit\Feeds;

use App\Feeds\Feed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

abstract class FeedTestCase extends TestCase
{
    abstract protected function callFeed(): Feed;

    abstract protected function items(): Collection;

    #[Test]
    public function itHasTheFeedTitleInTheData(): void
    {
        $feedClass = $this->callFeed();
        $invadedFeed = invade($feedClass);

        $invadedFeed->items = collect();

        $data = $invadedFeed->makeData();

        $this->assertArrayHasKey('title', $data);
        $this->assertEquals($invadedFeed->feedTitle(), $data['title']);
    }

    #[Test]
    public function itHasTheFeedLinkInTheData(): void
    {
        $feedClass = $this->callFeed();
        $invadedFeed = invade($feedClass);

        $invadedFeed->items = collect();

        $data = $invadedFeed->makeData();

        $this->assertArrayHasKey('link', $data);
        $this->assertEquals($invadedFeed->linkRoot(), $data['link']);
    }

    #[Test]
    public function itHasTheFeedDescriptionInTheData(): void
    {
        $feedClass = $this->callFeed();
        $invadedFeed = invade($feedClass);

        $invadedFeed->items = collect();

        $data = $invadedFeed->makeData();

        $this->assertArrayHasKey('description', $data);
        $this->assertEquals($invadedFeed->feedDescription(), $data['description']);
    }

    #[Test]
    public function itHasTheDateInTheData(): void
    {
        $feedClass = $this->callFeed();
        $invadedFeed = invade($feedClass);

        $invadedFeed->items = collect();

        $data = $invadedFeed->makeData();

        $this->assertArrayHasKey('date', $data);
    }

    #[Test]
    public function itHasTheItemsInTheData(): void
    {
        $feedClass = $this->callFeed();
        $invadedFeed = invade($feedClass);

        $invadedFeed->items = collect();

        $data = $invadedFeed->makeData();

        $this->assertArrayHasKey('items', $data);
    }

    protected function callFormatItem(Model $item): array
    {
        return invade($this->callFeed())->formatItem($item);
    }
}
