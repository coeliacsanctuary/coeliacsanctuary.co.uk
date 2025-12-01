<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Journey;

use App\DataObjects\Journey\QueuedEventData;
use App\Enums\Journey\EventType;
use App\Jobs\Journey\LogPageEventJob;
use App\Models\Journeys\Event;
use App\Models\Journeys\Journey;
use App\Models\Journeys\PageEvent;
use App\Models\Journeys\PageView;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogPageEventJobTest extends TestCase
{
    protected Journey $journey;

    protected PageView $pageView;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journey = $this->create(Journey::class);
        $this->pageView = $this->create(PageView::class, ['journey_id' => $this->journey->id]);
    }

    #[Test]
    public function itCreatesAnEventRecordForThatPath(): void
    {
        $this->assertDatabaseEmpty(Event::class);

        (new LogPageEventJob($this->dataFactory(eventType: EventType::CLICKED, eventIdentifier: 'bar')))->handle();

        $this->assertDatabaseCount(Event::class, 1);
        $this->assertDatabaseHas(Event::class, ['event_type' => EventType::CLICKED, 'element' => 'bar']);
    }

    #[Test]
    public function itDoesntCreateAnEventRecordForThatEventIfOneExists(): void
    {
        $this->create(Event::class, ['event_type' => EventType::CLICKED, 'element' => 'bar']);

        (new LogPageEventJob($this->dataFactory(eventType: EventType::CLICKED, eventIdentifier: 'bar')))->handle();

        $this->assertDatabaseCount(Event::class, 1);
    }

    #[Test]
    public function itLogsTheEventAgainstThePageView(): void
    {
        $this->assertDatabaseEmpty(PageEvent::class);

        (new LogPageEventJob($this->dataFactory()))->handle();

        $this->assertDatabaseCount(PageEvent::class, 1);

        /** @var PageEvent $event */
        $event = PageEvent::query()->first();

        $this->assertTrue($event->pageView->is($this->pageView));
        $this->assertTrue($event->page->is($this->pageView->page));
        $this->assertTrue($this->pageView->events->first()->is($event));
    }

    #[Test]
    public function itUpdatesTheJourneyUpdatedAtTimestamp(): void
    {
        $this->journey->update(['updated_at' => now()->subMinute(1)]);
        $timestamp = now()->subSecond()->getTimestamp();

        (new LogPageEventJob($this->dataFactory(timestamp: $timestamp)))->handle();

        $this->journey->refresh();

        $this->assertEquals($this->journey->updated_at->timestamp, $timestamp);
    }

    protected function dataFactory(?string $journeyId = null, ?string $pageViewId = null, ?EventType $eventType = null, string $eventIdentifier = 'div.foo', array $data = [], ?int $timestamp = null): QueuedEventData
    {
        return new QueuedEventData(
            $journeyId ?? $this->journey->id,
            $pageViewId ?? $this->pageView->id,
            $eventType ?? EventType::CLICKED,
            $eventIdentifier ?? Str::random(),
            $data,
            $timestamp ?? now()->subSeconds(5)->getTimestamp(),
        );
    }
}
