<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\CheckEateryRecommendationWithAiAction;
use App\Jobs\EatingOut\SendEateryRecommendationToAiJob;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryRecommendationAiData;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckEateryRecommendationWithAiActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
    }

    #[Test]
    public function itDispatchesTheSendToAiJob(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        $this->callAction(CheckEateryRecommendationWithAiAction::class, $recommendation);

        Bus::assertDispatched(SendEateryRecommendationToAiJob::class);
    }

    #[Test]
    public function itCreatesAnEmptyAiDataRowBeforeDispatching(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        $this->assertDatabaseEmpty(EateryRecommendationAiData::class);

        $this->callAction(CheckEateryRecommendationWithAiAction::class, $recommendation);

        $this->assertDatabaseCount(EateryRecommendationAiData::class, 1);
    }

    #[Test]
    public function itResetsAnExistingAiDataRowBeforeDispatching(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        $this->create(EateryRecommendationAiData::class, [
            'wheretoeat_place_recommendation_id' => $recommendation->id,
            'place_name' => 'Some Cafe',
            'explanation' => 'Previously populated.',
            'is_eligible' => true,
        ]);

        $this->callAction(CheckEateryRecommendationWithAiAction::class, $recommendation);

        $this->assertDatabaseCount(EateryRecommendationAiData::class, 1);

        $aiData = $recommendation->aiData()->first();

        $this->assertNull($aiData->place_name);
        $this->assertNull($aiData->explanation);
        $this->assertNull($aiData->is_eligible);
        $this->assertNull($aiData->completed_at);
    }
}
