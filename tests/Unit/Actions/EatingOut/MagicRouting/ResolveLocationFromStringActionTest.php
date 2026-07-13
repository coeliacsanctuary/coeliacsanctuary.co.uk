<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut\MagicRouting;

use App\Actions\EatingOut\MagicRouting\ResolveLocationFromStringAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResolveLocationFromStringActionTest extends TestCase
{
    #[Test]
    public function itReturnsNullWhenNoMatchIsFound(): void
    {
        $result = $this->callAction(ResolveLocationFromStringAction::class, 'non-existent-slug');

        $this->assertNull($result);
    }

    #[Test]
    public function itResolvesACountyBySlug(): void
    {
        $county = $this->create(EateryCounty::class, ['slug' => 'test-county']);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);
        $this->create(Eatery::class, ['town_id' => $town->id]);

        $result = $this->callAction(ResolveLocationFromStringAction::class, 'test-county');

        $this->assertInstanceOf(EateryCounty::class, $result);
        $this->assertSame($county->id, $result->id);
    }

    #[Test]
    public function itResolvesATownBySlug(): void
    {
        $town = $this->create(EateryTown::class, ['slug' => 'test-town']);
        $this->create(Eatery::class, ['town_id' => $town->id]);

        $result = $this->callAction(ResolveLocationFromStringAction::class, 'test-town');

        $this->assertInstanceOf(EateryTown::class, $result);
        $this->assertSame($town->id, $result->id);
    }

    #[Test]
    public function itResolvesAnAreaBySlug(): void
    {
        $area = $this->create(EateryArea::class, ['slug' => 'test-area']);
        $this->create(Eatery::class, ['area_id' => $area->id]);

        $result = $this->callAction(ResolveLocationFromStringAction::class, 'test-area');

        $this->assertInstanceOf(EateryArea::class, $result);
        $this->assertSame($area->id, $result->id);
    }

    #[Test]
    public function itPrioritisesCountyOverTownWithTheSameSlug(): void
    {
        $county = $this->create(EateryCounty::class, ['slug' => 'shared-slug']);
        $supportingTown = $this->create(EateryTown::class, ['county_id' => $county->id]);
        $this->create(Eatery::class, ['town_id' => $supportingTown->id]);

        $town = $this->create(EateryTown::class, ['slug' => 'shared-slug']);
        $this->create(Eatery::class, ['town_id' => $town->id]);

        $result = $this->callAction(ResolveLocationFromStringAction::class, 'shared-slug');

        $this->assertInstanceOf(EateryCounty::class, $result);
        $this->assertSame($county->id, $result->id);
    }

    #[Test]
    public function itPrioritisesTownOverAreaWithTheSameSlug(): void
    {
        $town = $this->create(EateryTown::class, ['slug' => 'shared-slug']);
        $this->create(Eatery::class, ['town_id' => $town->id]);

        $area = $this->create(EateryArea::class, ['slug' => 'shared-slug']);
        $this->create(Eatery::class, ['area_id' => $area->id]);

        $result = $this->callAction(ResolveLocationFromStringAction::class, 'shared-slug');

        $this->assertInstanceOf(EateryTown::class, $result);
        $this->assertSame($town->id, $result->id);
    }
}
