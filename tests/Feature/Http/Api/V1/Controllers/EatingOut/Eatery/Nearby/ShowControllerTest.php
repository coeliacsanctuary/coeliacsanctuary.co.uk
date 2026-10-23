<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Eatery\Nearby;

use App\Actions\EatingOut\GetNearbyEateriesAction;
use App\Models\EatingOut\Eatery;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.details.nearby', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itCallsTheGetNearbyEateriesActionWithTheEatery(): void
    {
        $this->expectAction(GetNearbyEateriesAction::class, [
            fn (Eatery $eatery) => $eatery->is($this->eatery),
        ], return: collect());

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itReturnsTheNearbyEateriesFromTheAction(): void
    {
        $nearbyEateries = collect([
            ['id' => 1, 'name' => 'Foo'],
            ['id' => '2-3', 'name' => 'Bar'],
        ]);

        $this->expectAction(GetNearbyEateriesAction::class, return: $nearbyEateries);

        $this->makeRequest()
            ->assertOk()
            ->assertExactJson(['data' => ['eateries' => $nearbyEateries->toArray()]]);
    }

    protected function makeRequest(string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.details.nearby', $this->eatery),
            ['x-coeliac-source' => $source],
        );
    }
}
