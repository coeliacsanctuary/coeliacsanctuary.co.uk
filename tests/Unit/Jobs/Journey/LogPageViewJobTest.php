<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Journey;

use App\DataObjects\Journey\QueuedPageViewData;
use App\Jobs\Journey\LogPageViewJob;
use App\Models\Journeys\Journey;
use App\Models\Journeys\Page;
use App\Models\Journeys\PageView;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogPageViewJobTest extends TestCase
{
    #[Test]
    public function itCreatesTheJourneyRecord(): void
    {
        $this->assertDatabaseEmpty(Journey::class);

        (new LogPageViewJob($this->dataFactory(journeyId: 'foo', sessionId: 'bar')))->handle();

        $this->assertDatabaseCount(Journey::class, 1);
        $this->assertDatabaseHas(Journey::class, ['id' => 'foo', 'session_id' => 'bar']);
    }

    #[Test]
    public function itUsesAnExistingJourneyRecordIfOneMatchesTheParameters(): void
    {
        $journey = $this->create(Journey::class);

        (new LogPageViewJob($this->dataFactory(journeyId: $journey->id, sessionId: $journey->session_id)))->handle();

        $this->assertDatabaseCount(Journey::class, 1);
    }

    #[Test]
    public function itCreatesAPageRecordForThatPath(): void
    {
        $this->assertDatabaseEmpty(Page::class);

        (new LogPageViewJob($this->dataFactory(path: 'some/page')))->handle();

        $this->assertDatabaseCount(Page::class, 1);
        $this->assertDatabaseHas(Page::class, ['path' => 'some/page']);
    }

    #[Test]
    public function itDoesntCreateAPageRecordForThatPathIfOneExists(): void
    {
        $this->create(Page::class, ['path' => 'some/page']);

        (new LogPageViewJob($this->dataFactory(path: 'some/page')))->handle();

        $this->assertDatabaseCount(Page::class, 1);
    }

    #[Test]
    public function itCreatesThePageViewAgainstTheJourney(): void
    {
        $this->assertDatabaseEmpty(PageView::class);

        (new LogPageViewJob($this->dataFactory()))->handle();

        $this->assertDatabaseCount(PageView::class, 1);

        $pageView = PageView::query()->first();
        $journey = Journey::query()->first();
        $page = Page::query()->first();

        $this->assertTrue($pageView->journey->is($journey));
        $this->assertTrue($pageView->page->is($page));
    }

    #[Test]
    public function ifTheJourneyWasNewlyCreatedItDoesntUpdateTheUpdatedAtColumn(): void
    {
        (new LogPageViewJob($this->dataFactory()))->handle();

        $journey = Journey::query()->first();

        $this->assertTrue($journey->created_at->eq($journey->updated_at));
    }

    #[Test]
    public function ifTheJourneyWasAlreadyCreatedThenItUpdatesTheUpdatedAt(): void
    {
        $journey = $this->create(Journey::class, ['updated_at' => now()->subMinute(1)]);
        $timestamp = now()->subSecond()->getTimestamp();

        (new LogPageViewJob($this->dataFactory(journeyId: $journey->id, sessionId: $journey->session_id, timestamp: $timestamp)))->handle();

        $journey->refresh();

        $this->assertEquals($journey->updated_at->timestamp, $timestamp);
    }

    protected function dataFactory(?string $pageViewId = null, ?string $journeyId = null, ?string $sessionId = null, string $path = '/', ?int $timestamp = null): QueuedPageViewData
    {
        return new QueuedPageViewData(
            $pageViewId ?? Str::uuid()->toString(),
            $journeyId ?? Str::uuid()->toString(),
            $sessionId ?? Str::random(),
            $path,
            $timestamp ?? now()->subSeconds(5)->getTimestamp(),
        );
    }
}
