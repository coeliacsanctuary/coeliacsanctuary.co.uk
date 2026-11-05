<?php

declare(strict_types=1);

namespace Tests\Unit\Resources\EatingOut;

use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Resources\EatingOut\EateryTripResource;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryTripResourceTest extends TestCase
{
    #[Test]
    public function itSerialisesAnEateryWithItsRoutePositionAndDetails(): void
    {
        $eatery = new Eatery()->forceFill(['id' => 12, 'name' => 'Gluten Free Cafe', 'reviews_count' => 3, 'reviews_avg_rating' => '4.3333']);
        $eatery->setRelation('town', new EateryTown()->forceFill(['town' => 'Crewe']));

        $pendingEatery = new PendingEatery(id: 12, branchId: null, ordering: 11119.6, lat: 53.1, lng: -2.45, distance: 667.7, typeId: 1, venueTypeId: 5, durationAlongRoute: 599.5);

        $this->assertSame([
            'key' => '12',
            'id' => 12,
            'isNationwideBranch' => false,
            'location' => ['lat' => 53.1, 'lng' => -2.45],
            'color' => '#80CCFC',
            'typeId' => 1,
            'venueTypeId' => 5,
            'branchId' => null,
            'name' => 'Gluten Free Cafe',
            'town' => 'Crewe',
            'chain' => null,
            'rating' => 4.3,
            'reviewsCount' => 3,
            'distanceAlongRoute' => 11120.0,
            'distanceFromRoute' => 668.0,
            'durationAlongRoute' => 600.0,
        ], new EateryTripResource($pendingEatery, $eatery)->toArray(new Request()));
    }

    #[Test]
    public function itIncludesTheChainNameForANationwideBranchWithoutReviews(): void
    {
        $branch = new NationwideBranch()->forceFill(['id' => 34, 'name' => 'Crewe Branch', 'reviews_count' => 0, 'reviews_avg_rating' => null]);
        $branch->setRelation('town', new EateryTown()->forceFill(['town' => 'Crewe']));
        $branch->setRelation('eatery', new Eatery()->forceFill(['name' => 'Chain Restaurant']));

        $result = new EateryTripResource(new PendingEatery(id: 12, branchId: 34, ordering: 0, typeId: 1), $branch)->toArray(new Request());

        $this->assertSame([34, 'Crewe Branch', 'Crewe', 'Chain Restaurant', null, 0], [$result['branchId'], $result['name'], $result['town'], $result['chain'], $result['rating'], $result['reviewsCount']]);
    }

    #[Test]
    public function itUsesTheChainNameForABranchWithoutItsOwnName(): void
    {
        $branch = new NationwideBranch()->forceFill(['id' => 35, 'name' => null]);
        $branch->setRelation('town', new EateryTown()->forceFill(['town' => 'Crewe']));
        $branch->setRelation('eatery', new Eatery()->forceFill(['name' => "Nando's"]));

        $result = new EateryTripResource(new PendingEatery(id: 12, branchId: 35, ordering: 0, typeId: 1), $branch)->toArray(new Request());

        $this->assertSame(["Nando's", 'Crewe', null], [$result['name'], $result['town'], $result['chain']]);
    }
}
