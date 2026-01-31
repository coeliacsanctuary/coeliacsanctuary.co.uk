<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\CheckEateryWebsiteAction;
use App\Models\EatingOut\Eatery;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckEateryWebsiteActionTest extends TestCase
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
    public function itReturnsSuccessForA200Response(): void
    {
        Http::fake([
            '*' => Http::response('OK', 200),
        ]);

        $result = $this->callAction(CheckEateryWebsiteAction::class, $this->eatery);

        $this->assertTrue($result->success);
        $this->assertEquals(200, $result->statusCode);
        $this->assertNull($result->errorMessage);
    }

    #[Test]
    public function itReturnsSuccessForOther2xxResponses(): void
    {
        Http::fake([
            '*' => Http::response('OK', 201),
        ]);

        $result = $this->callAction(CheckEateryWebsiteAction::class, $this->eatery);

        $this->assertTrue($result->success);
        $this->assertEquals(201, $result->statusCode);
    }

    #[Test]
    public function itReturnsFailureForA404Response(): void
    {
        Http::fake([
            '*' => Http::response('Not Found', 404),
        ]);

        $result = $this->callAction(CheckEateryWebsiteAction::class, $this->eatery);

        $this->assertFalse($result->success);
        $this->assertEquals(404, $result->statusCode);
        $this->assertEquals('HTTP 404 response', $result->errorMessage);
    }

    #[Test]
    public function itReturnsFailureForA500Response(): void
    {
        Http::fake([
            '*' => Http::response('Server Error', 500),
        ]);

        $result = $this->callAction(CheckEateryWebsiteAction::class, $this->eatery);

        $this->assertFalse($result->success);
        $this->assertEquals(500, $result->statusCode);
        $this->assertEquals('HTTP 500 response', $result->errorMessage);
    }

    #[Test]
    public function itReturnsFailureForConnectionErrors(): void
    {
        Http::fake([
            '*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('Connection timed out'),
        ]);

        $result = $this->callAction(CheckEateryWebsiteAction::class, $this->eatery);

        $this->assertFalse($result->success);
        $this->assertEquals(500, $result->statusCode);
        $this->assertStringContainsString('Connection failed', $result->errorMessage);
    }

    #[Test]
    public function itReturnsFailureWhenEateryHasNoWebsite(): void
    {
        $this->eatery->update(['website' => null]);

        $result = $this->callAction(CheckEateryWebsiteAction::class, $this->eatery->refresh());

        $this->assertFalse($result->success);
        $this->assertNull($result->statusCode);
        $this->assertEquals('No website URL configured', $result->errorMessage);
    }
}
