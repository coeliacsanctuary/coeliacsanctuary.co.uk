<?php

declare(strict_types=1);

namespace Tests\Unit\Support\EatingOut\SuggestEdits\Fields;

use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use App\Support\EatingOut\SuggestEdits\Fields\FeaturesField;
use Database\Seeders\EateryScaffoldingSeeder;
use Tests\TestCase;

class FeaturesFieldTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itReturnsTheDatabaseValue(): void
    {
        $field = app(FeaturesField::class);

        /** @var string $currentValue */
        $currentValue = $field->getCurrentValue($this->eatery);

        $this->assertJson($currentValue);
    }

    #[Test]
    public function itReturnsThePreparedValue(): void
    {
        $field = FeaturesField::make(1);

        $this->assertEquals(1, $field->prepare());
    }

    #[Test]
    public function itReturnsTheValueForDisplay(): void
    {
        $feature = EateryFeature::query()->first();

        $data = [[
            'key' => $feature->id,
            'label' => $feature->feature,
            'selected' => true,
        ]];

        $field = FeaturesField::make($data);

        $this->assertEquals(json_encode($data), $field->getSuggestedValue());
    }

    #[Test]
    public function itCanCommitTheSuggestedValue(): void
    {
        $allFeatures = EateryFeature::query()->get();

        $payload = $allFeatures->map(fn (EateryFeature $feature, int $index) => [
            'key' => $index + 1,
            'label' => $feature->feature,
            'selected' => $index > 2,
        ]);

        $this->eatery->features()->attach($allFeatures->first());

        $field = FeaturesField::make(json_encode($payload));

        $this->assertCount(1, $this->eatery->features);
        $this->assertTrue($this->eatery->features->contains('id', $allFeatures->first()->id));
        $this->assertFalse($this->eatery->features->contains('id', $allFeatures->last()->id));

        $field->commitSuggestedValue($this->eatery);

        $this->eatery->refresh();

        $this->assertCount(2, $this->eatery->features);

        $this->assertFalse($this->eatery->features->contains('id', $allFeatures->first()->id));
        $this->assertTrue($this->eatery->features->contains('id', $allFeatures->last()->id));
    }
}
