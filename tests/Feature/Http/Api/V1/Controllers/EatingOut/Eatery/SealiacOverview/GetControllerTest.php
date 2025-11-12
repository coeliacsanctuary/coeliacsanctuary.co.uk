<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Eatery\SealiacOverview;

use App\Actions\EatingOut\GetSealiacEateryOverviewAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
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
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.details.sealiac-overview.get', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itReturnsNotFoundIfTheEateryIsMarkedAsClosedDown(): void
    {
        $this->eatery->update(['closed_down' => true]);

        $this->makeRequest()->assertNotFound();
    }

    #[Test]
    public function itCallsTheGetSealiacEateryOverviewActionAction(): void
    {
        $this->mock(GetSealiacEateryOverviewAction::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(function ($eatery, $branch) {
                $this->assertInstanceOf(Eatery::class, $eatery);
                $this->assertTrue($eatery->is($this->eatery));
                $this->assertNull($branch);

                return true;
            })
            ->andReturn($this->create(SealiacOverview::class));

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function ifTheRequestHasABranchIdButItDoesntExistItWillReturnNotFound(): void
    {
        $this->makeRequest(['branchId' => 123])->assertNotFound();
    }

    #[Test]
    public function ifTheRequestHasABranchIdForAnotherEateryItWillReturnNotFound(): void
    {
        $branch = $this->create(NationwideBranch::class);

        $this->makeRequest(['branchId' => $branch->id])->assertNotFound();
    }

    #[Test]
    public function ifTheRequestHasABranchIdAndTheBranchExistsItWillCallTheActionWithThatBranch(): void
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]);

        $this->mock(GetSealiacEateryOverviewAction::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(function ($eatery, $paramBranch) use ($branch) {
                $this->assertInstanceOf(NationwideBranch::class, $paramBranch);
                $this->assertTrue($paramBranch->is($branch));

                return true;
            })
            ->andReturn($this->create(SealiacOverview::class));

        $this->makeRequest(['branchId' => $branch->id])->assertOk();
    }

    #[Test]
    public function itReturnsTheExpectedDataFormat(): void
    {
        $this->mock(GetSealiacEateryOverviewAction::class)
            ->shouldReceive('handle')
            ->andReturn($this->create(SealiacOverview::class));

        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'overview',
                'id',
            ]]);
    }

    protected function makeRequest(array $params = [], string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.details.sealiac-overview.get', ['eatery' => $this->eatery, ...$params]),
            ['x-coeliac-source' => $source],
        );
    }
}
