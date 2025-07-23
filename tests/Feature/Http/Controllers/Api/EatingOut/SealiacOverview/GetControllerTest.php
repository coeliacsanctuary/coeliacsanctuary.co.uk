<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\EatingOut\SealiacOverview;

use App\Actions\EatingOut\GetSealiacEateryOverviewAction;
use App\Actions\SealiacOverview\FormatResponseAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
use Exception;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    protected Eatery $eatery;

    protected NationwideBranch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eatery = $this->create(Eatery::class);
        $this->branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->eatery->id,
        ]);

        OpenAI::fake();
    }

    #[Test]
    public function itReturnsNotFoundIfTheEateryDoesntExist(): void
    {
        $this->getJson(route('api.wheretoeat.sealiac.get', ['eatery' => 123]))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheEateryIsntLive(): void
    {
        $this->eatery->update(['live' => false]);

        $this->getJson(route('api.wheretoeat.sealiac.get', $this->eatery))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheEateryIsLiveButHasClosedDown(): void
    {
        $this->eatery->update(['closed_down' => true]);

        $this->getJson(route('api.wheretoeat.sealiac.get', $this->eatery))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheRequestIncludesABranchIdButItDoesntExist(): void
    {
        $this->getJson(route('api.wheretoeat.sealiac.get', ['eatery' => $this->eatery, 'branchId' => 123]))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheRequestIncludesABranchIdButItIsntLive(): void
    {
        $this->branch->update(['live' => false]);

        $this->getJson(route('api.wheretoeat.sealiac.get', ['eatery' => $this->eatery, 'branchId' => $this->branch->id]))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheRequestIncludesABranchIdButItDoesntBelongToTheParentEatery(): void
    {
        $this->branch->update(['wheretoeat_id' => 123]);

        $this->getJson(route('api.wheretoeat.sealiac.get', ['eatery' => $this->eatery, 'branchId' => $this->branch->id]))->assertNotFound();
    }

    #[Test]
    public function itCallsTheGetSealiacEateryOverviewActionWithTheEatery(): void
    {
        $this->mock(GetSealiacEateryOverviewAction::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(function ($eatery) {
                $this->assertTrue($this->eatery->is($eatery));

                return true;
            })
            ->andReturn($this->create(SealiacOverview::class));

        $this->getJson(route('api.wheretoeat.sealiac.get', $this->eatery))->assertOk();
    }

    #[Test]
    public function itCallsTheGetSealiacEateryOverviewActionWithTheEateryAndBranchIfOneIsInTheRequest(): void
    {
        $this->mock(GetSealiacEateryOverviewAction::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(function ($eatery, $branch) {
                $this->assertTrue($this->eatery->is($eatery));
                $this->assertTrue($this->branch->is($branch));

                return true;
            })
            ->andReturn($this->create(SealiacOverview::class));

        $this->getJson(route('api.wheretoeat.sealiac.get', ['eatery' => $this->eatery, 'branchId' => $this->branch->id]))->assertOk();
    }

    #[Test]
    public function itReturnsJsonWithADataAttribute(): void
    {
        $this->mock(GetSealiacEateryOverviewAction::class)
            ->shouldReceive('handle')
            ->andReturn($this->create(SealiacOverview::class));

        $this->getJson(route('api.wheretoeat.sealiac.get', $this->eatery))->assertJsonStructure(['data' => ['overview', 'id']]);
    }

    #[Test]
    public function itCallsTheFormatResponseAction(): void
    {
        $overview = $this->create(SealiacOverview::class, ['overview' => 'this is the ai overview']);

        $this->mock(GetSealiacEateryOverviewAction::class)
            ->shouldReceive('handle')
            ->andReturn($overview);

        $this->mock(FormatResponseAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($argResponse) {
                $this->assertEquals('this is the ai overview', $argResponse);

                return true;
            })
            ->once()
            ->andReturn(Str::of('foo'));

        $this->getJson(route('api.wheretoeat.sealiac.get', $this->eatery))->assertExactJson(['data' => [
            'overview' => Str::of('foo'),
            'id' => $overview->id,
        ]]);
    }

    #[Test]
    public function itCanHandleTheActionErroringAndReturnsAsNotFound(): void
    {
        $this->mock(GetSealiacEateryOverviewAction::class)
            ->shouldReceive('handle')
            ->andThrow(new Exception());

        $this->getJson(route('api.wheretoeat.sealiac.get', $this->eatery))->assertNotFound();
    }
}
