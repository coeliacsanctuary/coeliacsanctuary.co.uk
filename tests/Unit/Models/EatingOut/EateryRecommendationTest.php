<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryRecommendationAiData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryRecommendationTest extends TestCase
{
    #[Test]
    public function itHasAnAiDataRelationship(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        $aiData = $this->build(EateryRecommendationAiData::class)->on($recommendation)->create();

        $this->assertInstanceOf(EateryRecommendationAiData::class, $recommendation->aiData);
        $this->assertTrue($aiData->is($recommendation->aiData));
    }
}
