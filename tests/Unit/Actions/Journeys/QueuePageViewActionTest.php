<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Journeys;

use App\Actions\Journey\QueuePageViewAction;
use App\Jobs\Journey\LogPageViewJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueuePageViewActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
    }

    #[Test]
    public function itQueuesTheLogPageViewJob(): void
    {
        app(QueuePageViewAction::class)->handle('foo', 'bar', 'baz');

        Bus::assertDispatched(LogPageViewJob::class);
    }

    #[Test]
    public function itReturnsTheQueuedPageViewData(): void
    {
        Carbon::setTestNow('2025-01-01 00:00:00');

        $data = app(QueuePageViewAction::class)->handle('foo', 'bar', 'baz');

        Carbon::setTestNow('2025-01-01 00:00:01');

        $this->assertTrue(Str::isUuid($data->pageViewId));
        $this->assertEquals('foo', $data->journeyId);
        $this->assertEquals('bar', $data->sessionId);
        $this->assertEquals('baz', $data->url);
        $this->assertEquals(Carbon::parse('2025-01-01 00:00:00')->timestamp, $data->timestamp);
    }
}
