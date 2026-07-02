<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\PrepareRecommendedEatery;

use App\Ai\Tools\PrepareRecommendedEatery\EateryFeatureList;
use App\Models\EatingOut\EateryFeature;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryFeatureListTest extends TestCase
{
    #[Test]
    public function itReturnsAllFeaturesOrderedAlphabetically(): void
    {
        $this->create(EateryFeature::class, ['feature' => 'Parking']);
        $this->create(EateryFeature::class, ['feature' => 'Afternoon Tea']);
        $this->create(EateryFeature::class, ['feature' => 'Gluten Free Menu']);

        $result = json_decode((string) (new EateryFeatureList())->handle(new Request()), true);

        $this->assertEquals(['Afternoon Tea', 'Gluten Free Menu', 'Parking'], $result);
    }

    #[Test]
    public function itReturnsEmptyWhenNoFeaturesExist(): void
    {
        $result = json_decode((string) (new EateryFeatureList())->handle(new Request()), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itHasAnEmptySchema(): void
    {
        $this->assertEmpty((new EateryFeatureList())->schema(new JsonSchemaTypeFactory()));
    }
}
