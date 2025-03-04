<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\MailcoachSchedule;

use App\Models\MailcoachSchedule;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('mailcoach-sdk.incoming-key', 'foobar');
    }

    #[Test]
    public function itErrorsWithoutTheCorrectKey(): void
    {
        $this->postJson(route('api.mailcoach-schedule'))->assertForbidden();
    }

    #[Test]
    public function itErrorsWithoutATimeInTheRequest(): void
    {
        $this->postJson(route('api.mailcoach-schedule', ['key' => 'foobar']))->assertJsonValidationErrorFor('time');
    }

    #[Test]
    public function itErrorsIfTheTimeIsNotInTheFuture(): void
    {
        $this->postJson(route('api.mailcoach-schedule', ['key' => 'foobar']), [
            'time' => now()->subDay()->toDateTimeString(),
        ])->assertJsonValidationErrorFor('time');
    }

    #[Test]
    public function itCreatesARecord(): void
    {
        $this->assertDatabaseEmpty(MailcoachSchedule::class);

        $scheduledAt = now()->addHour()->toDateTimeString();

        $this->postJson(route('api.mailcoach-schedule', ['key' => 'foobar']), [
            'time' => $scheduledAt,
        ])->assertNoContent();

        $this->assertDatabaseCount(MailcoachSchedule::class, 1);

        $this->assertEquals(MailcoachSchedule::query()->first()->scheduled_at->toDateTimeString(), $scheduledAt);
    }
}
