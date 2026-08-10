<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetNearbyEateriesAction;
use App\DataObjects\EatingOut\LatLng;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\NationwideBranch;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetNearbyEateriesActionTest extends TestCase
{
    protected EateryCounty $county;

    /** @var array{lat: float, lng: float} */
    protected array $london = ['lat' => 51.5, 'lng' => -0.1];

    /** @var array{lat: float, lng: float} */
    protected array $edinburgh = ['lat' => 55.95, 'lng' => -3.18];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = $this->create(EateryCounty::class);
    }

    #[Test]
    public function itReturnsAnEateryNearAnotherEatery(): void
    {
        $eatery = $this->createEatery($this->london);

        $nearbyEatery = $this->createEatery($this->offset($this->london, 0.001));

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertCount(1, $result);
        $this->assertEquals($nearbyEatery->id, $result->first()['id']);
    }

    #[Test]
    public function itReturnsTheClosestEateriesFirst(): void
    {
        $eatery = $this->createEatery($this->london);

        $nearbyEatery = $this->createEatery($this->offset($this->london, 0.001));

        $farawayEatery = $this->createEatery($this->edinburgh);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertCount(2, $result);
        $this->assertEquals($nearbyEatery->id, $result->first()['id']);
        $this->assertEquals($farawayEatery->id, $result->last()['id']);
    }

    #[Test]
    public function itExcludesTheGivenEatery(): void
    {
        $eatery = $this->createEatery($this->london);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itReturnsABranchNearAnEatery(): void
    {
        $eatery = $this->createEatery($this->london);

        $parentEatery = $this->createNationwideEatery();
        $branch = $this->createBranch($this->offset($this->london, 0.001), $parentEatery);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertCount(1, $result);
        $this->assertEquals("{$parentEatery->id}-{$branch->id}", $result->first()['id']);
    }

    #[Test]
    public function itReturnsTheClosestBranchesToAnEateryFirst(): void
    {
        $eatery = $this->createEatery($this->london);

        $parentEatery = $this->createNationwideEatery();
        $nearbyBranch = $this->createBranch($this->offset($this->london, 0.001), $parentEatery);

        $farAwayBranch = $this->createBranch($this->edinburgh, $parentEatery);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertCount(2, $result);
        $this->assertEquals("{$parentEatery->id}-{$nearbyBranch->id}", $result->first()['id']);
        $this->assertEquals("{$parentEatery->id}-{$farAwayBranch->id}", $result->last()['id']);
    }

    #[Test]
    public function itReturnsABranchNearAnotherBranch(): void
    {
        $branch = $this->createBranch($this->london);

        $nearbyBranch = $this->createBranch($this->offset($this->london, 0.001));

        $result = app(GetNearbyEateriesAction::class)->handle($branch);

        $this->assertCount(1, $result);
        $this->assertEquals("{$nearbyBranch->wheretoeat_id}-{$nearbyBranch->id}", $result->first()['id']);
    }

    #[Test]
    public function itReturnsTheClosestBranchesToAnotherBranchFirst(): void
    {
        $branch = $this->createBranch($this->london);

        $nearbyBranch = $this->createBranch($this->offset($this->london, 0.001));

        $farawayBranch = $this->createBranch($this->edinburgh);

        $result = app(GetNearbyEateriesAction::class)->handle($branch);

        $this->assertCount(2, $result);
        $this->assertEquals("{$nearbyBranch->wheretoeat_id}-{$nearbyBranch->id}", $result->first()['id']);
        $this->assertEquals("{$farawayBranch->wheretoeat_id}-{$farawayBranch->id}", $result->last()['id']);
    }

    #[Test]
    public function itExcludesTheGivenBranch(): void
    {
        $branch = $this->createBranch($this->london);

        $result = app(GetNearbyEateriesAction::class)->handle($branch);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itReturnsAnEateryNearABranch(): void
    {
        $eatery = $this->createEatery($this->offset($this->london, 0.001));

        $branch = $this->createBranch($this->london);

        $result = app(GetNearbyEateriesAction::class)->handle($branch);

        $this->assertCount(1, $result);
        $this->assertEquals($eatery->id, $result->first()['id']);
    }

    #[Test]
    public function itReturnsTheClosestEateriesToABranchFirst(): void
    {
        $eatery = $this->createEatery($this->offset($this->london, 0.001));

        $branch = $this->createBranch($this->london);

        $farAwayEatery = $this->createEatery($this->edinburgh);

        $result = app(GetNearbyEateriesAction::class)->handle($branch);

        $this->assertCount(2, $result);
        $this->assertEquals($eatery->id, $result->first()['id']);
        $this->assertEquals($farAwayEatery->id, $result->last()['id']);
    }

    #[Test]
    public function itReturnsAMixtureOfEateriesAndBranches(): void
    {
        $eatery = $this->createEatery($this->london);

        $nearbyEatery = $this->createEatery($this->offset($this->london, 0.001));

        $nearbyBranch = $this->createBranch($this->offset($this->london, 0.002));

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertCount(2, $result);

        $ids = $result->pluck('id')->toArray();

        $this->assertContains($nearbyEatery->id, $ids);
        $this->assertContains("{$nearbyBranch->wheretoeat_id}-{$nearbyBranch->id}", $ids);
    }

    #[Test]
    public function itReturnsTheClosestRecordsFirstAcrossBothTypes(): void
    {
        $eatery = $this->createEatery($this->london);

        $this->build(Eatery::class)->count(4)->create([
            ...$this->offset($this->london, 0.002),
            'county_id' => $this->county->id,
        ]);

        $nearbyBranch = $this->createBranch($this->offset($this->london, 0.001));

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertCount(5, $result);
        $this->assertEquals("{$nearbyBranch->wheretoeat_id}-{$nearbyBranch->id}", $result->first()['id']);
    }

    #[Test]
    public function itDoesntReturnNationwideEateries(): void
    {
        $eatery = $this->createEatery($this->london);

        $this->create(Eatery::class, [
            ...$this->offset($this->london, 0.001),
            'county_id' => 1,
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itDoesntReturnNationwideBranches(): void
    {
        $eatery = $this->createEatery($this->london);

        $this->create(NationwideBranch::class, [
            ...$this->offset($this->london, 0.001),
            'wheretoeat_id' => $this->createNationwideEatery()->id,
            'county_id' => 1,
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itOnlyReturnsSixRecordsByDefault(): void
    {
        $eatery = $this->createEatery($this->london);

        $this->build(Eatery::class)->count(7)->create([
            ...$this->offset($this->london, 0.001),
            'county_id' => $this->county->id,
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertCount(6, $result);
    }

    #[Test]
    public function itCanBeGivenACustomLimit(): void
    {
        $eatery = $this->createEatery($this->london);

        $this->build(Eatery::class)->count(5)->create([
            ...$this->offset($this->london, 0.001),
            'county_id' => $this->county->id,
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery, 2);

        $this->assertCount(2, $result);
    }

    #[Test]
    public function itCanHandleARawLatLngBeingPassedInAndFindResults(): void
    {
        $nearbyEatery = $this->createEatery($this->offset($this->london, 0.001));

        $nearbyBranch = $this->createBranch($this->offset($this->london, 0.002));

        $result = app(GetNearbyEateriesAction::class)->handle(new LatLng($this->london['lat'], $this->london['lng']));

        $this->assertCount(2, $result);

        $ids = $result->pluck('id')->toArray();

        $this->assertContains($nearbyEatery->id, $ids);
        $this->assertContains("{$nearbyBranch->wheretoeat_id}-{$nearbyBranch->id}", $ids);
    }

    #[Test]
    public function itUsesTheSnippetForTheInfoWhenItIsSet(): void
    {
        $eatery = $this->createEatery($this->london);

        $this->createEatery($this->offset($this->london, 0.001), [
            'snippet' => 'This is the snippet',
            'info' => 'This is the info',
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertEquals('This is the snippet', $result->first()['info']);
    }

    #[Test]
    public function itFallsBackToTheTruncatedInfoWhenThereIsNoSnippet(): void
    {
        $eatery = $this->createEatery($this->london);

        $info = Str::repeat('word ', 50);

        $this->createEatery($this->offset($this->london, 0.001), [
            'snippet' => null,
            'info' => $info,
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertEquals(Str::limit($info, 125, preserveWords: true), $result->first()['info']);
    }

    #[Test]
    public function itUsesTheParentEaterysSnippetForABranch(): void
    {
        $eatery = $this->createEatery($this->london);

        $parentEatery = $this->createNationwideEatery(['snippet' => 'The parent eatery snippet']);

        $this->createBranch($this->offset($this->london, 0.001), $parentEatery);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertEquals('The parent eatery snippet', $result->first()['info']);
    }

    #[Test]
    public function itUsesTheBranchNameForABranchWithItsOwnName(): void
    {
        $eatery = $this->createEatery($this->london);

        $parentEatery = $this->createNationwideEatery(['name' => 'Pho']);

        $this->createBranch($this->offset($this->london, 0.001), $parentEatery, ['name' => 'Manchester Piccadilly']);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertEquals('Manchester Piccadilly', $result->first()['name']);
    }

    #[Test]
    public function itFallsBackToTheParentEateryNameForABranchWithAnEmptyName(): void
    {
        $eatery = $this->createEatery($this->london);

        $parentEatery = $this->createNationwideEatery(['name' => 'Pho']);

        $this->createBranch($this->offset($this->london, 0.001), $parentEatery, ['name' => '']);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertEquals('Pho', $result->first()['name']);
    }

    #[Test]
    public function itFallsBackToTheParentEateryNameForABranchWithNoName(): void
    {
        $eatery = $this->createEatery($this->london);

        $parentEatery = $this->createNationwideEatery(['name' => 'Pho']);

        $this->createBranch($this->offset($this->london, 0.001), $parentEatery, ['name' => null]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertEquals('Pho', $result->first()['name']);
    }

    #[Test]
    public function itReturnsEachResultInTheExpectedFormat(): void
    {
        $eatery = $this->createEatery($this->london);

        $this->createEatery($this->offset($this->london, 0.001));

        $this->createBranch($this->offset($this->london, 0.002));

        $results = app(GetNearbyEateriesAction::class)->handle($eatery);

        $expectedKeys = ['id', 'name', 'address', 'info', 'link', 'distance', 'ratings_count', 'average_rating'];

        foreach ($results as $result) {
            $this->assertArrayHasKeys($expectedKeys, $result);
        }
    }

    /**
     * @param  array{lat: float, lng: float}  $latLng
     * @param  array<string, mixed>  $attributes
     */
    protected function createEatery(array $latLng = [], array $attributes = []): Eatery
    {
        return $this->create(Eatery::class, [
            ...$latLng,
            ...$attributes,
            'county_id' => $this->county->id,
        ]);
    }

    /**
     * The parent of a nationwide branch is always a nationwide eatery, so it is
     * never itself a nearby result.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function createNationwideEatery(array $attributes = []): Eatery
    {
        return $this->create(Eatery::class, [...$attributes, 'county_id' => 1]);
    }

    /**
     * @param  array{lat: float, lng: float}  $latLng
     * @param  array<string, mixed>  $attributes
     */
    protected function createBranch(array $latLng = [], ?Eatery $eatery = null, array $attributes = []): NationwideBranch
    {
        return $this->create(NationwideBranch::class, [
            ...$latLng,
            ...$attributes,
            'wheretoeat_id' => $eatery?->id ?? $this->createNationwideEatery()->id,
            'county_id' => $this->county->id,
        ]);
    }

    /**
     * @param  array{lat: float, lng: float}  $latLng
     * @return array{lat: float, lng: float}
     */
    protected function offset(array $latLng, float $by): array
    {
        return ['lat' => $latLng['lat'] + $by, 'lng' => $latLng['lng']];
    }
}
