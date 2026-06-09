<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\EatingOut;

use App\Jobs\EatingOut\CheckSingleEateryWebsiteJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCheck;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckSingleEateryWebsiteJobTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class, ['website' => 'https://example.com']);
    }

    #[Test]
    public function itUpdatesCheckTimestampOnSuccess(): void
    {
        Http::fake([
            '*' => Http::response('OK', 200),
        ]);

        $this->assertNull($this->eatery->check);

        (new CheckSingleEateryWebsiteJob($this->eatery))->handle();

        $this->eatery->refresh();

        $this->assertNotNull($this->eatery->check);
        $this->assertNotNull($this->eatery->check->website_checked_at);
    }

    #[Test]
    public function itUpdatesCheckTimestampOnFailure(): void
    {
        Http::fake([
            '*' => Http::response('Not Found', 404),
        ]);

        (new CheckSingleEateryWebsiteJob($this->eatery))->handle();

        $this->eatery->refresh();

        $this->assertNotNull($this->eatery->check);
        $this->assertNotNull($this->eatery->check->website_checked_at);
    }

    #[Test]
    public function itCreatesAlertOnFailure(): void
    {
        Http::fake([
            '*' => Http::response('Not Found', 404),
        ]);

        $this->assertCount(0, $this->eatery->alerts);

        (new CheckSingleEateryWebsiteJob($this->eatery))->handle();

        $this->eatery->refresh();

        $this->assertCount(1, $this->eatery->alerts);

        $alert = $this->eatery->alerts->first();
        $this->assertEquals('website', $alert->type);
        $this->assertStringContainsString('404', $alert->details);
    }

    #[Test]
    public function itDoesNotCreateAlertOnSuccess(): void
    {
        Http::fake([
            '*' => Http::response('OK', 200),
        ]);

        (new CheckSingleEateryWebsiteJob($this->eatery))->handle();

        $this->eatery->refresh();

        $this->assertCount(0, $this->eatery->alerts);
    }

    #[Test]
    public function itUpdatesExistingCheckRecord(): void
    {
        Http::fake([
            '*' => Http::response('OK', 200),
        ]);

        $existingCheck = $this->build(EateryCheck::class)->create([
            'wheretoeat_id' => $this->eatery->id,
            'website_checked_at' => now()->subDays(60),
            'google_checked_at' => now()->subDays(30),
        ]);

        (new CheckSingleEateryWebsiteJob($this->eatery))->handle();

        $this->eatery->refresh();

        $this->assertEquals(1, EateryCheck::query()->where('wheretoeat_id', $this->eatery->id)->count());
        $this->assertTrue($this->eatery->check->website_checked_at->isToday());
        $this->assertFalse($this->eatery->check->google_checked_at->isToday());
    }
}
