<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Enums\EatingOut\EateryMagicRouteType;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\EatingOut\EateryTown;
use App\Services\EatingOut\Collection\Configuration;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryMagicRouteRecordTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itCastsTheResolverTypeToAnEnum(): void
    {
        $record = $this->create(EateryMagicRouteRecord::class);

        $this->assertInstanceOf(EateryMagicRouteType::class, $record->resolver_type);
        $this->assertSame(EateryMagicRouteType::HundredPercentGlutenFree, $record->resolver_type);
    }

    #[Test]
    public function itCastsTheBodyToAnArray(): void
    {
        $record = $this->create(EateryMagicRouteRecord::class, [
            'body' => ['foo' => 'bar'],
        ]);

        $this->assertSame(['foo' => 'bar'], $record->fresh()->body);
    }

    #[Test]
    public function itCastsTheBuilderConfigToAConfiguration(): void
    {
        $record = $this->create(EateryMagicRouteRecord::class);

        $this->assertInstanceOf(Configuration::class, $record->fresh()->builder_config);
    }

    #[Test]
    public function itResolvesTheLocationForACounty(): void
    {
        $this->create(Eatery::class); // makes the seeded county + town live

        $county = EateryCounty::query()->firstOrFail();

        $record = $this->create(EateryMagicRouteRecord::class, [
            'location_type' => EateryCounty::class,
            'location_id' => $county->id,
        ]);

        $this->assertInstanceOf(EateryCounty::class, $record->location);
        $this->assertTrue($county->is($record->location));
    }

    #[Test]
    public function itResolvesTheLocationForATown(): void
    {
        $this->create(Eatery::class);

        $town = EateryTown::query()->firstOrFail();

        $record = $this->create(EateryMagicRouteRecord::class, [
            'location_type' => EateryTown::class,
            'location_id' => $town->id,
        ]);

        $this->assertInstanceOf(EateryTown::class, $record->location);
        $this->assertTrue($town->is($record->location));
    }

    #[Test]
    public function itResolvesTheLocationForAnArea(): void
    {
        $area = $this->create(EateryArea::class);
        $this->create(Eatery::class, ['area_id' => $area->id]);

        $record = $this->create(EateryMagicRouteRecord::class, [
            'location_type' => EateryArea::class,
            'location_id' => $area->id,
        ]);

        $this->assertInstanceOf(EateryArea::class, $record->location);
        $this->assertTrue($area->is($record->location));
    }

    #[Test]
    public function itBuildsTheTitleForACounty(): void
    {
        $this->create(Eatery::class);

        $county = EateryCounty::query()->firstOrFail();

        $record = $this->create(EateryMagicRouteRecord::class, [
            'location_type' => EateryCounty::class,
            'location_id' => $county->id,
        ]);

        $this->assertSame('Eating 100% Gluten Free in Cheshire', $record->title);
    }

    #[Test]
    public function itBuildsTheTitleForATown(): void
    {
        $this->create(Eatery::class);

        $town = EateryTown::query()->firstOrFail();

        $record = $this->create(EateryMagicRouteRecord::class, [
            'location_type' => EateryTown::class,
            'location_id' => $town->id,
        ]);

        $this->assertSame('Eating 100% Gluten Free in Crewe', $record->title);
    }

    #[Test]
    public function itBuildsTheTitleForAnArea(): void
    {
        $area = $this->create(EateryArea::class, ['area' => 'Soho']);
        $this->create(Eatery::class, ['area_id' => $area->id]);

        $record = $this->create(EateryMagicRouteRecord::class, [
            'location_type' => EateryArea::class,
            'location_id' => $area->id,
        ]);

        $this->assertSame('Eating 100% Gluten Free in Soho', $record->title);
    }

    #[Test]
    public function itGeneratesTheLinkFromTheRawLocation(): void
    {
        $record = $this->create(EateryMagicRouteRecord::class, [
            'raw_location' => 'cheshire',
        ]);

        $this->assertSame('/eating-100-percent-gluten-free-in-cheshire', $record->link());
    }
}
