<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetGroupedEateryNationwideBranchesAction;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Resources\EatingOut\NationwideBranchResource;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Http\Resources\Json\JsonResource;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetGroupedEateryNationwideBranchesActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    protected function handle(string $formatter = NationwideBranchResource::class): array
    {
        return app(GetGroupedEateryNationwideBranchesAction::class)
            ->handle(NationwideBranch::with(['country', 'county', 'town', 'area'])->get(), $formatter);
    }

    #[Test]
    public function itGroupsBranchesByCountry(): void
    {
        $country2 = $this->create(EateryCountry::class, ['country' => 'Scotland']);
        $county2 = $this->create(EateryCounty::class, ['country_id' => $country2->id]);
        $town2 = $this->create(EateryTown::class, ['county_id' => $county2->id]);

        $this->create(NationwideBranch::class);
        $this->create(NationwideBranch::class, ['country_id' => $country2->id, 'county_id' => $county2->id, 'town_id' => $town2->id]);

        $result = $this->handle();

        $this->assertArrayHasKey('England', $result);
        $this->assertArrayHasKey('Scotland', $result);
    }

    #[Test]
    public function itGroupsBranchesByCountyWithinEachCountry(): void
    {
        $county2 = $this->create(EateryCounty::class, ['county' => 'Yorkshire', 'country_id' => 1]);
        $town2 = $this->create(EateryTown::class, ['county_id' => $county2->id]);

        $this->create(NationwideBranch::class);
        $this->create(NationwideBranch::class, ['county_id' => $county2->id, 'town_id' => $town2->id]);

        $result = $this->handle();

        $this->assertArrayHasKey('Cheshire', $result['England']);
        $this->assertArrayHasKey('Yorkshire', $result['England']);
    }

    #[Test]
    public function itGroupsBranchesByTownWithinEachCounty(): void
    {
        $town2 = $this->create(EateryTown::class, ['town' => 'Nantwich', 'county_id' => 1]);

        $this->create(NationwideBranch::class);
        $this->create(NationwideBranch::class, ['town_id' => $town2->id]);

        $result = $this->handle();

        $this->assertArrayHasKey('Crewe', $result['England']['Cheshire']);
        $this->assertArrayHasKey('Nantwich', $result['England']['Cheshire']);
    }

    #[Test]
    public function itGroupsBranchesByAreaWithinEachTown(): void
    {
        $area = $this->create(EateryArea::class, ['area' => 'Town Centre', 'town_id' => 1]);

        $this->create(NationwideBranch::class);
        $this->create(NationwideBranch::class, ['area_id' => $area->id]);

        $result = $this->handle();

        $this->assertArrayHasKey('_', $result['England']['Cheshire']['Crewe']);
        $this->assertArrayHasKey('Town Centre', $result['England']['Cheshire']['Crewe']);
    }

    #[Test]
    public function branchesWithNoAreaAreGroupedUnderAnUnderscore(): void
    {
        $this->create(NationwideBranch::class, ['area_id' => null]);

        $result = $this->handle();

        $this->assertArrayHasKey('_', $result['England']['Cheshire']['Crewe']);
    }

    #[Test]
    public function itSortsBranchesAlphabeticallyByNameWithinArea(): void
    {
        $this->create(NationwideBranch::class, ['name' => 'Zebra Branch']);
        $this->create(NationwideBranch::class, ['name' => 'Alpha Branch']);
        $this->create(NationwideBranch::class, ['name' => 'Middle Branch']);

        $branches = $this->handle()['England']['Cheshire']['Crewe']['_'];

        $names = collect($branches)->map(fn (JsonResource $resource) => $resource->resource->name);

        $this->assertEquals(['Alpha Branch', 'Middle Branch', 'Zebra Branch'], $names->values()->all());
    }

    #[Test]
    public function itUsesNationwideBranchResourceAsTheDefaultFormatter(): void
    {
        $this->create(NationwideBranch::class);

        $branch = $this->handle()['England']['Cheshire']['Crewe']['_'][0];

        $this->assertInstanceOf(NationwideBranchResource::class, $branch);
    }

    #[Test]
    public function itCanAcceptACustomFormatter(): void
    {
        $customFormatter = new class (null) extends JsonResource {
        };

        $this->create(NationwideBranch::class);

        $branch = $this->handle($customFormatter::class)['England']['Cheshire']['Crewe']['_'][0];

        $this->assertInstanceOf($customFormatter::class, $branch);
    }
}
