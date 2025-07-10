<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\EatingOut\SealiacOverview;

use App\Actions\EatingOut\GetSealiacEateryOverviewAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
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
            ->andReturn('foo');

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
            ->andReturn('foo');

        $this->getJson(route('api.wheretoeat.sealiac.get', ['eatery' => $this->eatery, 'branchId' => $this->branch->id]))->assertOk();
    }

    #[Test]
    public function itReturnsJsonWithADataAttribute(): void
    {
        $this->mock(GetSealiacEateryOverviewAction::class)
            ->shouldReceive('handle')
            ->andReturn('foo');

        $this->getJson(route('api.wheretoeat.sealiac.get', $this->eatery))->assertJsonStructure(['data']);
    }

    #[Test]
    public function itReturnsTheResponseOfTheGetSealiacEateryOverviewActionAsTheDataPropertyAsMarkdownWithTheCorrectAdditions(): void
    {
        $this->mock(GetSealiacEateryOverviewAction::class)
            ->shouldReceive('handle')
            ->andReturn('this is the ai overview');

        $expectedResult = Str::of('this is the ai overview')
            ->markdown([
                'renderer' => [
                    'soft_break' => '<br />',
                ],
            ])
            ->replaceFirst('<p>', '<p><span class="quote-elem open"><span>&ldquo;</span></span>')
            ->replaceLast('<p>', '<p><span class="quote-elem close"><span>&rdquo;</span></span>');

        $this->getJson(route('api.wheretoeat.sealiac.get', $this->eatery))->assertExactJson(['data' => $expectedResult]);
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
