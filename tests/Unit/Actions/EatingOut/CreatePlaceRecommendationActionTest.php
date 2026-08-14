<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Jobs\EatingOut\SendEateryRecommendationToAiJob;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\EatingOut\CreatePlaceRecommendationAction;
use App\Models\EatingOut\EateryRecommendation;
use Tests\RequestFactories\EateryRecommendAPlaceRequestFactory;
use Tests\TestCase;

class CreatePlaceRecommendationActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
    }

    #[Test]
    public function itCreatesThePlaceRecommendation(): void
    {
        $data = EateryRecommendAPlaceRequestFactory::new()->create();

        $this->assertDatabaseEmpty(EateryRecommendation::class);

        $this->callAction(CreatePlaceRecommendationAction::class, $data);

        $this->assertDatabaseCount(EateryRecommendation::class, 1);
    }

    #[Test]
    public function itDispatchesTheSendToAiJob(): void
    {
        $data = EateryRecommendAPlaceRequestFactory::new()->create();

        $this->callAction(CreatePlaceRecommendationAction::class, $data);

        Bus::assertDispatched(SendEateryRecommendationToAiJob::class);
    }
}
