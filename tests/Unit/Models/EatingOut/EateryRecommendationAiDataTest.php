<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryRecommendationAiData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryRecommendationAiDataTest extends TestCase
{
    #[Test]
    public function itHasARecommendationRelationship(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        $aiData = $this->build(EateryRecommendationAiData::class)->on($recommendation)->create();

        $this->assertInstanceOf(EateryRecommendation::class, $aiData->recommendation);
        $this->assertTrue($recommendation->is($aiData->recommendation));
    }

    #[Test]
    public function itCastsIsEligibleToBoolean(): void
    {
        $aiData = $this->create(EateryRecommendationAiData::class);

        $this->assertIsBool($aiData->is_eligible);
        $this->assertTrue($aiData->is_eligible);
    }

    #[Test]
    public function itCastsIsEligibleToBooleanWhenIneligible(): void
    {
        $aiData = $this->build(EateryRecommendationAiData::class)->ineligible()->create();

        $this->assertIsBool($aiData->is_eligible);
        $this->assertFalse($aiData->is_eligible);
    }

    #[Test]
    public function itCastsFeaturesToArray(): void
    {
        $aiData = $this->build(EateryRecommendationAiData::class)
            ->state(['features' => ['Gluten Free Menu', 'Parking']])
            ->create();

        $this->assertIsArray($aiData->features);
        $this->assertEquals(['Gluten Free Menu', 'Parking'], $aiData->features);
    }
}
