<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Eatery\OpeningTimes;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryOpeningTimes;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);

        $this->build(EateryOpeningTimes::class)->forEatery($this->eatery)->create();
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.details.opening-times', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itReturnsNotFoundIfTheEateryDoesntHaveOpeningTimes(): void
    {
        $this->eatery->openingTimes()->delete();

        $this->makeRequest()->assertNotFound();
    }

    #[Test]
    public function itReturnsTheExpectedDataFormat(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'is_open_now',
                'today' => ['opens', 'closes'],
                'days',
            ]]);
    }

    protected function makeRequest(string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.details.opening-times', $this->eatery),
            ['x-coeliac-source' => $source],
        );
    }
}
