<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries\Steps;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\LatLng;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EaterySearchTerm;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\GetEateries\Steps\AppendDistanceToBranches;
use App\Pipelines\EatingOut\GetEateries\Steps\AppendDistanceToEateries;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesFromFiltersAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInLatLngRadiusAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInSearchAreaAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInTownAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesFromFiltersAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInLatLngAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInTownAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\PaginateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\RelateEateriesAndBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseBrowseResultsAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseResultsAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SortPendingEateriesAction;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

abstract class GetEateriesTestCase extends TestCase
{
    use WithFaker;

    protected EateryCounty $county;

    protected EateryTown $town;

    protected EaterySearchTerm $eaterySearchTerm;

    protected LatLng $latLng;

    protected int $eateriesToCreate = 5;

    protected int $reviewsToCreate = 5;

    protected int $branchesToCreate = 5;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eaterySearchTerm = $this->create(EaterySearchTerm::class, [
            'term' => 'London',
        ]);

        $this->latLng = new LatLng(
            lat: 55.5,
            lng: -0.1,
            radius: 5,
        );

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();
        $this->town = EateryTown::query()->withoutGlobalScopes()->first();

        $this->build(Eatery::class)
            ->count($this->eateriesToCreate)
            ->sequence(fn (Sequence $sequence) => ['id' => $sequence->index + 1])
            ->create([
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
                'venue_type_id' => EateryVenueType::query()->first()->id,
            ]);

        EateryFeature::query()->first()->eateries()->attach(Eatery::query()->first());

        $this->county->eateries->each(function (Eatery $eatery, $index): void {
            $this->build(EateryReview::class)
                ->count($this->reviewsToCreate - $index)
                ->create([
                    'wheretoeat_id' => $eatery->id,
                    'rating' => random_int(1, 5),
                    'approved' => true,
                ]);
        });

        if ($this->branchesToCreate) {
            $this->build(NationwideBranch::class)
                ->count($this->branchesToCreate)
                ->create([
                    'county_id' => $this->county->id,
                    'town_id' => $this->town->id,
                ]);
        }
    }

    protected function callGetEateriesInTownAction(Collection $eateries = new Collection(), array $filters = []): ?GetEateriesPipelineData
    {
        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $pipelineData = new GetEateriesPipelineData(
            town: $this->town,
            filters: $filters,
            eateries: $eateries,
        );

        $this->callAction(GetEateriesInTownAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callGetEateriesFromFiltersAction(Collection $eateries = new Collection(), array $filters = []): ?GetEateriesPipelineData
    {
        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $pipelineData = new GetEateriesPipelineData(
            town: $this->town,
            filters: $filters,
            eateries: $eateries,
        );

        $this->callAction(GetEateriesFromFiltersAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callGetEateriesInSearchAreaAction(Collection $eateries = new Collection(), array $filters = []): ?GetEateriesPipelineData
    {
        Eatery::query()->update([
            'lat' => 55.5,
            'lng' => -0.1,
        ]);

        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $pipelineData = new GetEateriesPipelineData(
            searchTerm: $this->eaterySearchTerm,
            filters: $filters,
            eateries: $eateries,
        );

        $this->callAction(GetEateriesInSearchAreaAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callGetEateriesInLatLngAction(Collection $eateries = new Collection(), array $filters = []): ?GetEateriesPipelineData
    {
        Eatery::query()->update([
            'lat' => 55.5,
            'lng' => -0.1,
        ]);

        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $pipelineData = new GetEateriesPipelineData(
            latLng: $this->latLng,
            filters: $filters,
            eateries: $eateries,
        );

        $this->callAction(GetEateriesInLatLngRadiusAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callGetBranchesAction(Collection $eateries = new Collection(), array $filters = []): ?GetEateriesPipelineData
    {
        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $pipelineData = new GetEateriesPipelineData(
            town: $this->town,
            filters: $filters,
            eateries: $eateries,
        );

        $this->callAction(GetNationwideBranchesInTownAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callGetBranchesFromFiltersAction(Collection $eateries = new Collection(), array $filters = []): ?GetEateriesPipelineData
    {
        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $pipelineData = new GetEateriesPipelineData(
            town: $this->town,
            filters: $filters,
            eateries: $eateries,
        );

        $this->callAction(GetNationwideBranchesFromFiltersAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callGetBranchesInLatLngRadiusAction(Collection $eateries = new Collection(), array $filters = []): ?GetEateriesPipelineData
    {
        NationwideBranch::query()->update([
            'lat' => 55.5,
            'lng' => -0.1,
        ]);

        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $pipelineData = new GetEateriesPipelineData(
            latLng: $this->latLng,
            filters: $filters,
            eateries: $eateries,
        );

        $this->callAction(GetNationwideBranchesInLatLngAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callSortEateriesAction(Collection $eateries): ?GetEateriesPipelineData
    {
        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $pipelineData = new GetEateriesPipelineData(
            town: $this->town,
            filters: [],
            eateries: $eateries,
        );

        $this->callAction(SortPendingEateriesAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callPaginateEateriesAction(?Collection $eateries = null): ?GetEateriesPipelineData
    {
        if ( ! $eateries) {
            $eateries = $this->callGetEateriesInTownAction()?->eateries;
            $eateries = $this->callGetBranchesAction($eateries)?->eateries;
        }

        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $pipelineData = new GetEateriesPipelineData(
            town: $this->town,
            filters: [],
            eateries: $eateries,
        );

        $this->callAction(PaginateEateriesAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callHydrateEateriesAction(?Collection $eateries = null): ?GetEateriesPipelineData
    {
        if ( ! $eateries) {
            $eateries = $this->callGetEateriesInTownAction()?->eateries;
            $eateries = $this->callGetBranchesAction($eateries)?->eateries;
        }

        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $pipelineData = new GetEateriesPipelineData(
            town: $this->town,
            filters: [],
            eateries: $eateries,
            paginator: $this->callPaginateEateriesAction($eateries)?->paginator
        );

        $this->callAction(HydrateEateriesAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callAppendDistanceToEateriesMethod(Collection $eateries, Collection $hydrated): ?GetEateriesPipelineData
    {
        $eateries = $eateries->map(function (PendingEatery $eatery) {
            $eatery->distance = $this->faker->randomFloat();

            return $eatery;
        });

        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $pipelineData = new GetEateriesPipelineData(
            town: $this->town,
            filters: [],
            eateries: $eateries,
            paginator: $this->callPaginateEateriesAction($eateries)?->paginator,
            hydrated: $hydrated,
        );

        $this->callAction(AppendDistanceToEateries::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callHydrateBranchesAction(?Collection $eateries = null): ?GetEateriesPipelineData
    {
        if ( ! $eateries) {
            $eateries = $this->callGetEateriesInTownAction()?->eateries;
            $eateries = $this->callGetBranchesAction($eateries)?->eateries;
        }

        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $pipelineData = new GetEateriesPipelineData(
            town: $this->town,
            filters: [],
            eateries: $eateries,
            paginator: $this->callPaginateEateriesAction($eateries)?->paginator
        );

        $this->callAction(HydrateBranchesAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callAppendDistanceToBranchesMethod(Collection $eateries, Collection $hydrated): ?GetEateriesPipelineData
    {
        $hydrated = $hydrated->map(function (NationwideBranch $eatery, $index) {
            //            $eatery->branchId = $index + 1;
            $eatery->distance = $this->faker->randomFloat();

            return $eatery;
        });

        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $pipelineData = new GetEateriesPipelineData(
            town: $this->town,
            filters: [],
            eateries: $eateries,
            paginator: $this->callPaginateEateriesAction($eateries)?->paginator,
            hydratedBranches: $hydrated,
        );

        $this->callAction(AppendDistanceToBranches::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callCheckForMissingEateriesAction(GetEateriesPipelineData $pipelineData): ?GetEateriesPipelineData
    {
        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $this->callAction(CheckForMissingEateriesAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callRelateEateriesAndBranchesAction(GetEateriesPipelineData $pipelineData): ?GetEateriesPipelineData
    {
        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $this->callAction(RelateEateriesAndBranchesAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callSerialiseResultsAction(): ?GetEateriesPipelineData
    {
        $eatery = Eatery::query()->first();
        $branch = NationwideBranch::query()->first();

        $eateries = collect([new PendingEatery(id: $eatery->id, branchId: $branch->id, ordering: 'abc')]);

        $pipelineData = new GetEateriesPipelineData(
            town: $this->town,
            filters: [],
            eateries: $eateries,
            paginator: $eateries->paginate(5),
            hydrated: $this->callHydrateEateriesAction($eateries)->hydrated,
            hydratedBranches: $this->callHydrateBranchesAction($eateries)->hydratedBranches
        );

        $pipelineData = $this->callRelateEateriesAndBranchesAction($pipelineData);

        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $this->callAction(SerialiseResultsAction::class, $pipelineData, $closure);

        return $toReturn;
    }

    protected function callSerialiseBrowseResultsAction(): ?GetEateriesPipelineData
    {
        $eatery = Eatery::query()->first();
        $branch = NationwideBranch::query()->first();

        $eateries = collect([new PendingEatery(id: $eatery->id, branchId: $branch->id, lat: $eatery->lat, lng: $eatery->lng, ordering: 'abc')]);

        $pipelineData = new GetEateriesPipelineData(
            latLng: $this->latLng,
            filters: [],
            eateries: $eateries,
        );

        $toReturn = null;

        $closure = function (GetEateriesPipelineData $pipelineData) use (&$toReturn): void {
            $toReturn = $pipelineData;
        };

        $this->callAction(SerialiseBrowseResultsAction::class, $pipelineData, $closure);

        return $toReturn;
    }
}
