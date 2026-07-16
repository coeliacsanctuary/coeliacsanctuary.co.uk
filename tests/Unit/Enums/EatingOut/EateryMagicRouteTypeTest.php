<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\EatingOut;

use App\Enums\EatingOut\EateryMagicRouteType;
use App\Services\EatingOut\Collection\Configuration;
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
}
