<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries;

use PHPUnit\Framework\Attributes\Test;
use App\DataObjects\EatingOut\LatLng;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\GetEateries\BrowseEateriesPipeline;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInLatLngRadiusAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInLatLngAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseBrowseResultsAction;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BrowseEateriesPipelineTest extends TestCase
{
    protected LatLng $latLng;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        Http::preventStrayRequests();
        $london = ['lat' => 51.50, 'lon' => 0.12, 'display_name' => 'London', 'type' => 'administrative'];

        Eatery::query()->update([
            'lat' => $london['lat'],
            'lng' => $london['lon'],
        ]);

        $this->latLng = new LatLng(
            lat: $london['lat'],
            lng: $london['lon'],
            radius: 5,
        );

        $eateries = $this->build(Eatery::class)
            ->count(10)
            ->sequence(fn (Sequence $sequence): array => ['id' => $sequence->index + 1])
            ->create([
                'venue_type_id' => EateryVenueType::query()->first()->id,
                'lat' => $london['lat'],
                'lng' => $london['lon'],
            ]);

        EateryFeature::query()->first()->eateries()->attach(Eatery::query()->first());

        $eateries->each(function (Eatery $eatery, $index): void {
            $this->build(EateryReview::class)
                ->count(10 - $index)
                ->create([
                    'wheretoeat_id' => $eatery->id,
                    'rating' => random_int(1, 5),
                    'approved' => true,
                ]);
        });

        $this->build(NationwideBranch::class)
            ->count(10)
            ->create([
                'lat' => $london['lat'],
                'lng' => $london['lon'],
            ]);

    }

    #[Test]
    public function itCallsTheActions(): void
    {
        $this->expectPipelineToExecute(GetEateriesInLatLngRadiusAction::class);
        $this->expectPipelineToExecute(GetNationwideBranchesInLatLngAction::class);
        $this->expectPipelineToExecute(SerialiseBrowseResultsAction::class);

        $this->runPipeline(BrowseEateriesPipeline::class, $this->latLng, []);
    }
}
