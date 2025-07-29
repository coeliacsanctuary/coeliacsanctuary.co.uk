<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\EateryDetails;

use App\Actions\EatingOut\ComputeEateryBackLinkAction;
use App\Actions\EatingOut\GetNearbyEateriesAction;
use App\Actions\EatingOut\LoadCompleteEateryDetailsForRequestAction;
use App\Models\EatingOut\EateryArea;
use Inertia\Support\Header;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $town;

    protected EateryArea $area;

    protected Eatery $eatery;

    protected NationwideBranch $nationwideBranch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();
        $this->town = EateryTown::query()->withoutGlobalScopes()->first();
        $this->area = $this->create(EateryArea::class, ['town_id' => $this->town->id]);

        $this->eatery = $this->create(Eatery::class);

        $this->nationwideBranch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->eatery->id,
        ]);

        Bus::fake(CreateEatingOutOpenGraphImageJob::class);
    }

    #[Test]
    public function itReturnsNotFoundForAnEateryThatDoesntExist(): void
    {
        $this->get(route('eating-out.show', ['county' => $this->county, 'town' => $this->town, 'eatery' => 'foo']))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundForAnEateryThatIsNotLive(): void
    {
        $eatery = $this->build(Eatery::class)->notLive()->create();

        $this->get(route('eating-out.show', ['county' => $this->county, 'town' => $this->town, 'eatery' => $eatery->slug]))->assertNotFound();
    }

    #[Test]
    public function itReturnsOkForALiveEatery(): void
    {
        $this->visitEatery()->assertOk();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageActionWhenVistingAnEatery(): void
    {
        $this->expectAction(GetEatingOutOpenGraphImageAction::class);

        $this->visitEatery();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitEatery()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/Details')
                    ->has('eatery')
                    ->where('eatery.name', $this->eatery->name)
                    ->etc()
            );
    }

    #[Test]
    public function itHasTheNearbyByEateriesInADeferredResponseWhenVisitingAnEatery(): void
    {
        $this->mock(GetNearbyEateriesAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($eatery) {
                $this->assertTrue($this->eatery->is($eatery));

                return true;
            })
            ->andReturn(collect())
            ->once();

        $this->visitEatery([
            Header::PARTIAL_ONLY => 'nearbyEateries',
            Header::PARTIAL_COMPONENT => 'EatingOut/Details',
        ])
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/Details')
                    ->has('nearbyEateries')
                    ->etc()
            );
    }

    #[Test]
    public function itReturnsHttpGoneIfTheLocationHasClosedDown(): void
    {
        $this->eatery->update(['closed_down' => true]);

        $this->visitEatery()->assertGone();
    }

    #[Test]
    public function itCallsTheComputeBackLinkAction(): void
    {
        $this->expectAction(ComputeEateryBackLinkAction::class, return: ['foo', 'bar']);

        $this->visitEatery();
    }

    #[Test]
    public function itCallsTheLoadCompleteEateryDetailsForRequestAction(): void
    {
        $this->expectAction(LoadCompleteEateryDetailsForRequestAction::class, return: ['foo', 'bar']);

        $this->visitEatery();
    }

    protected function convertToNationwideEatery(): self
    {
        $this->eatery->county->update(['county' => 'Nationwide']);
        $this->eatery->town->update(['town' => 'nationwide']);

        return $this;
    }

    #[Test]
    public function itReturnsOkForALiveNationwideEatery(): void
    {
        $this
            ->convertToNationwideEatery()
            ->visitNationwideEatery()
            ->assertOk();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageActionForANationwideEatery(): void
    {
        $this->expectAction(GetEatingOutOpenGraphImageAction::class);

        $this
            ->convertToNationwideEatery()
            ->visitNationwideEatery();
    }

    #[Test]
    public function itRendersTheNationwideInertiaPage(): void
    {
        $this
            ->convertToNationwideEatery()
            ->visitNationwideEatery()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/Details')
                    ->has('eatery')
                    ->where('eatery.name', $this->eatery->name)
                    ->etc()
            );
    }

    #[Test]
    public function itHasTheNearbyByEateriesInADeferredResponseWhenVisitingANationwidePage(): void
    {
        $this->mock(GetNearbyEateriesAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($eatery) {
                $this->assertTrue($this->eatery->is($eatery));

                return true;
            })
            ->andReturn(collect())
            ->once();

        $this
            ->convertToNationwideEatery()
            ->visitNationwideEatery([
                Header::PARTIAL_ONLY => 'nearbyEateries',
                Header::PARTIAL_COMPONENT => 'EatingOut/Details',
            ])
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/Details')
                    ->has('nearbyEateries')
                    ->etc()
            );
    }

    #[Test]
    public function itCallsTheComputeBackLinkActionOnTheNationwidePage(): void
    {
        $this->expectAction(ComputeEateryBackLinkAction::class, return: ['foo', 'bar']);

        $this->convertToNationwideEatery()->visitNationwideEatery();
    }

    #[Test]
    public function itCallsTheLoadCompleteEateryDetailsForRequestActionOnTheNationwidePage(): void
    {
        $this->expectAction(LoadCompleteEateryDetailsForRequestAction::class, return: ['foo', 'bar']);

        $this->convertToNationwideEatery()->visitNationwideEatery();
    }

    #[Test]
    public function itReturnsOkForALiveBranch(): void
    {
        $this
            ->convertToNationwideEatery()
            ->visitBranch()
            ->assertOk();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageActionForANationwideBranch(): void
    {
        $this->expectAction(GetEatingOutOpenGraphImageAction::class);

        $this
            ->convertToNationwideEatery()
            ->visitBranch();
    }

    #[Test]
    public function itRendersTheBranchInertiaPage(): void
    {
        $this
            ->convertToNationwideEatery()
            ->visitBranch()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/Details')
                    ->has('eatery')
                    ->where('eatery.name', $this->eatery->name)
                    ->etc()
            );
    }

    #[Test]
    public function itHasTheNearbyByEateriesInADeferredResponseWhenVisitingABranch(): void
    {
        $this->mock(GetNearbyEateriesAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($eatery) {
                $this->assertTrue($this->eatery->is($eatery));

                return true;
            })
            ->andReturn(collect())
            ->once();

        $this
            ->convertToNationwideEatery()
            ->visitBranch([
                Header::PARTIAL_ONLY => 'nearbyEateries',
                Header::PARTIAL_COMPONENT => 'EatingOut/Details',
            ])
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/Details')
                    ->has('nearbyEateries')
                    ->etc()
            );
    }

    #[Test]
    public function itCallsTheComputeBackLinkActionOnBranchPage(): void
    {
        $this->expectAction(ComputeEateryBackLinkAction::class, return: ['foo', 'bar']);

        $this->convertToNationwideEatery()->visitBranch();
    }

    #[Test]
    public function itCallsTheLoadCompleteEateryDetailsForRequestActionOnBranchPage(): void
    {
        $this->expectAction(LoadCompleteEateryDetailsForRequestAction::class, return: ['foo', 'bar']);

        $this->convertToNationwideEatery()->visitBranch();
    }

    protected function convertToLondonEatery(): self
    {
        $this->eatery->county->update(['slug' => 'london', 'county' => 'london']);
        $this->eatery->update(['area_id' => $this->area->id]);

        return $this;
    }

    #[Test]
    public function itReturnsOkForALondonEatery(): void
    {
        $this
            ->convertToLondonEatery()
            ->visitLondonEatery()
            ->assertOk();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageActionForALondonEatery(): void
    {
        $this->expectAction(GetEatingOutOpenGraphImageAction::class);

        $this
            ->convertToLondonEatery()
            ->visitLondonEatery();
    }

    #[Test]
    public function itRendersTheLondonInertiaPage(): void
    {
        $this
            ->convertToLondonEatery()
            ->visitLondonEatery()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/Details')
                    ->has('eatery')
                    ->where('eatery.name', $this->eatery->name)
                    ->etc()
            );
    }

    #[Test]
    public function itHasTheNearbyByEateriesInADeferredResponseWhenVisitingALondonEatery(): void
    {
        $this->mock(GetNearbyEateriesAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($eatery) {
                $this->assertTrue($this->eatery->is($eatery));

                return true;
            })
            ->andReturn(collect())
            ->once();

        $this
            ->convertToLondonEatery()
            ->visitLondonEatery([
                Header::PARTIAL_ONLY => 'nearbyEateries',
                Header::PARTIAL_COMPONENT => 'EatingOut/Details',
            ])
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/Details')
                    ->has('nearbyEateries')
                    ->etc()
            );
    }

    #[Test]
    public function itCallsTheComputeBackLinkActionOnTheLondonPage(): void
    {
        $this->expectAction(ComputeEateryBackLinkAction::class, return: ['foo', 'bar']);

        $this->convertToLondonEatery()->visitLondonEatery();
    }

    #[Test]
    public function itCallsTheLoadCompleteEateryDetailsForRequestActionOnTheLondonPage(): void
    {
        $this->expectAction(LoadCompleteEateryDetailsForRequestAction::class, return: ['foo', 'bar']);

        $this->convertToLondonEatery()->visitLondonEatery();
    }

    protected function visitEatery(array $headers = []): TestResponse
    {
        return $this->get(route('eating-out.show', ['county' => $this->county, 'town' => $this->town, 'eatery' => $this->eatery->slug]), $headers);
    }

    protected function visitBranch(array $headers = []): TestResponse
    {
        return $this->get(route('eating-out.nationwide.show.branch', [
            'eatery' => $this->eatery->slug,
            'nationwideBranch' => $this->nationwideBranch->slug,
        ]), $headers);
    }

    protected function visitNationwideEatery(array $headers = []): TestResponse
    {
        return $this->get(route('eating-out.nationwide.show', ['eatery' => $this->eatery->slug]), $headers);
    }

    protected function visitLondonEatery(array $headers = []): TestResponse
    {
        return $this->get(route('eating-out.london.borough.area.show', ['town' => $this->town, 'area' => $this->area, 'eatery' => $this->eatery->slug]), $headers);
    }
}
