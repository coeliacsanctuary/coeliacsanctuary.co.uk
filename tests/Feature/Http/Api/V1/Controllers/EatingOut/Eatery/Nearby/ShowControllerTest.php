<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Eatery\Nearby;

use App\Actions\EatingOut\GetNearbyEateriesAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
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
    public function ifTheRequestHasABranchIdButItDoesntExistItWillReturnNotFound(): void
    {
        $this->dontExpectAction(GetNearbyEateriesAction::class);

        $this->makeRequest(['branchId' => 123])->assertNotFound();
    }

    #[Test]
    public function ifTheRequestHasABranchIdForAnotherEateryItWillReturnNotFound(): void
    {
        $branch = $this->create(NationwideBranch::class);

        $this->dontExpectAction(GetNearbyEateriesAction::class);

        $this->makeRequest(['branchId' => $branch->id])->assertNotFound();
    }

    #[Test]
    public function ifTheRequestHasABranchIdAndTheBranchExistsItWillCallTheActionWithThatBranch(): void
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]);

        $this->expectAction(GetNearbyEateriesAction::class, [
            fn (NationwideBranch $paramBranch) => $paramBranch->is($branch),
        ], return: collect());

        $this->makeRequest(['branchId' => $branch->id])->assertOk();
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

    protected function makeRequest(array $params = [], string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.details.nearby', ['eatery' => $this->eatery, ...$params]),
            ['x-coeliac-source' => $source],
        );
    }
}
