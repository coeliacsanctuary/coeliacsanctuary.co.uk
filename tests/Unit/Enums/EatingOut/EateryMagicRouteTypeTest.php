<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\EatingOut;

use App\Ai\Agents\MagicRoutes\HundredPercentGlutenFreeMagicRouteContentAgent;
use App\Ai\Agents\MagicRoutes\MagicRouteAgent;
use App\Contracts\RouteFallbackResolverContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\Enums\EatingOut\EateryMagicRouteType;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForMagicRoutePipeline;
use App\Services\EatingOut\Collection\Configuration;
use App\Support\MagicRouting\Resolvers\HundredPercentGlutenFreeEateriesFallbackResolver;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryMagicRouteTypeTest extends TestCase
{
    #[Test]
    public function itReturnsACallableForHundredPercentGlutenFree(): void
    {
        $this->assertIsCallable(EateryMagicRouteType::HundredPercentGlutenFree->builderConfiguration());
    }

    #[Test]
    public function itAddsTheCorrectJoinForHundredPercentGlutenFree(): void
    {
        $configuration = new Configuration();

        EateryMagicRouteType::HundredPercentGlutenFree->builderConfiguration()($configuration);

        $joins = $configuration->getJoins();

        $this->assertCount(1, $joins);
        $this->assertSame(
            ['wheretoeat_assigned_features', 'wheretoeat_assigned_features.wheretoeat_id', 'wheretoeat.id', null],
            $joins->first()->jsonSerialize(),
        );
    }

    #[Test]
    public function itAddsTheCorrectWhereForHundredPercentGlutenFree(): void
    {
        $configuration = new Configuration();

        EateryMagicRouteType::HundredPercentGlutenFree->builderConfiguration()($configuration);

        $wheres = $configuration->getWheres();

        $this->assertCount(1, $wheres);
        $this->assertSame(
            ['wheretoeat_assigned_features.feature_id', '=', 1, 'and'],
            $wheres->first()->jsonSerialize(),
        );
    }

    #[Test]
    public function itReturnsAMagicRouteAgentContractForHundredPercentGlutenFree(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $this->mock(GetEateriesForMagicRoutePipeline::class)
            ->shouldReceive('run')
            ->shouldReceive('rawData')
            ->andReturn(new GetEateriesPipelineData(filters: [], hydrated: collect()));

        $county = $this->create(EateryCounty::class);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);
        $this->create(Eatery::class, ['town_id' => $town->id, 'county_id' => $county->id]);

        $routeRecord = $this->create(EateryMagicRouteRecord::class, [
            'location_type' => EateryCounty::class,
            'location_id' => $county->id,
        ]);

        $agent = EateryMagicRouteType::HundredPercentGlutenFree->agent([$routeRecord]);

        $this->assertInstanceOf(MagicRouteAgent::class, $agent);
    }

    #[Test]
    public function itReturnsAHundredPercentGlutenFreeContentAgentForHundredPercentGlutenFree(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $this->mock(GetEateriesForMagicRoutePipeline::class)
            ->shouldReceive('run')
            ->shouldReceive('rawData')
            ->andReturn(new GetEateriesPipelineData(filters: [], hydrated: collect()));

        $county = $this->create(EateryCounty::class);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);
        $this->create(Eatery::class, ['town_id' => $town->id, 'county_id' => $county->id]);

        $routeRecord = $this->create(EateryMagicRouteRecord::class, [
            'location_type' => EateryCounty::class,
            'location_id' => $county->id,
        ]);

        $agent = EateryMagicRouteType::HundredPercentGlutenFree->agent([$routeRecord]);

        $this->assertInstanceOf(HundredPercentGlutenFreeMagicRouteContentAgent::class, $agent);
    }

    #[Test]
    public function itReturnsARouteFallbackResolverContractForHundredPercentGlutenFree(): void
    {
        $resolver = EateryMagicRouteType::HundredPercentGlutenFree->fallbackResolver();

        $this->assertInstanceOf(RouteFallbackResolverContract::class, $resolver);
    }

    #[Test]
    public function itReturnsAHundredPercentGlutenFreeEateriesFallbackResolverForHundredPercentGlutenFree(): void
    {
        $resolver = EateryMagicRouteType::HundredPercentGlutenFree->fallbackResolver();

        $this->assertInstanceOf(HundredPercentGlutenFreeEateriesFallbackResolver::class, $resolver);
    }
}
