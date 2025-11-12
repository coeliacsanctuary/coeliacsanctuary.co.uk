<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Nationwide;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.nationwide.index'))->assertForbidden();
    }

    #[Test]
    public function itReturnsADataProperty(): void
    {
        $this->makeRequest()->assertOk()->assertJsonStructure(['data' => []]);
    }

    #[Test]
    public function itReturnsANationwideEateryInTheResults(): void
    {
        $nationwide = $this->create(EateryCounty::class, ['slug' => 'nationwide']);

        $nationwideEatery = $this->create(Eatery::class, [
            'county_id' => $nationwide->id,
            'town_id' => $this->create(EateryTown::class, ['county_id' => $nationwide->id])->id,
        ]);

        $request = $this->makeRequest();

        $request->assertOk();

        $this->assertTrue($request->collect('data')->contains('id', $nationwideEatery->id));
    }

    #[Test]
    public function itItDoesntReturnANormalEateryInTheResults(): void
    {
        $county = $this->create(EateryCounty::class, ['slug' => 'foo']);

        $this->create(Eatery::class, [
            'county_id' => $county->id,
            'town_id' => $this->create(EateryTown::class, ['county_id' => $county->id])->id,
        ]);

        $this->makeRequest()
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    #[Test]
    public function itReturnsTheNumberOfBranchesInThatChain(): void
    {
        $nationwide = $this->create(EateryCounty::class, ['slug' => 'nationwide']);

        $nationwideEatery = $this->create(Eatery::class, [
            'county_id' => $nationwide->id,
            'town_id' => $this->create(EateryTown::class, ['county_id' => $nationwide->id])->id,
        ]);

        $this->create(NationwideBranch::class, 5, [
            'wheretoeat_id' => $nationwideEatery->id,
        ]);

        $request = $this->makeRequest();

        $request->assertOk();

        $result = $request->collect('data')->firstWhere('id', $nationwideEatery->id);

        $this->assertEquals(5, $result['number_of_branches']);
        $this->assertEquals(0, $result['nearby_branches']);
    }

    #[Test]
    public function itReturnsNearbyBranchesIfALatLngIsSentInTheRequest(): void
    {
        $nationwide = $this->create(EateryCounty::class, ['slug' => 'nationwide']);

        $nationwideEatery = $this->create(Eatery::class, [
            'county_id' => $nationwide->id,
            'town_id' => $this->create(EateryTown::class, ['county_id' => $nationwide->id])->id,
        ]);

        $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $nationwideEatery->id,
            'lat' => 51,
            'lng' => -1,
        ]);

        $request = $this->makeRequest('51,-1');

        $request->assertOk();

        $result = $request->collect('data')->firstWhere('id', $nationwideEatery->id);

        $this->assertEquals(1, $result['nearby_branches']);
    }

    protected function makeRequest(?string $latlng = null, string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.nationwide.index', $latlng ? ['latlng' => $latlng] : []),
            ['x-coeliac-source' => $source],
        );
    }
}
