<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\CheckForMailcoachScheduledEmailsCommand;
use App\Models\MailcoachSchedule;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckForMailcoachScheduledEmailsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
    }

    #[Test]
    public function itDoesntDoAnythingForSchedulesInTheFuture(): void
    {
        MailcoachSchedule::query()->create([
            'scheduled_at' => now()->addMinutes(2),
        ]);

        $this->assertDatabaseCount(MailcoachSchedule::class, 1);

        $this->artisan(CheckForMailcoachScheduledEmailsCommand::class);

        Http::assertNothingSent();

        $this->assertDatabaseCount(MailcoachSchedule::class, 1);
    }

    #[Test]
    public function itPingsTheMailcoachWebsiteWhenTheScheduleIsOneMinuteAway(): void
    {
        Carbon::setTestNow('2025-01-01 09:58:00');

        MailcoachSchedule::query()->create([
            'scheduled_at' => '2025-01-01 10:00:00'
        ]);

        $this->artisan(CheckForMailcoachScheduledEmailsCommand::class);

        Http::assertNothingSent();

        Carbon::setTestNow('2025-01-01 09:59:00');

        $this->artisan(CheckForMailcoachScheduledEmailsCommand::class);

        Http::assertSent(fn (Request $request) => $request->url() === config('mailcoach-sdk.endpoint'));

        $this->assertDatabaseEmpty(MailcoachSchedule::class);
    }
}
