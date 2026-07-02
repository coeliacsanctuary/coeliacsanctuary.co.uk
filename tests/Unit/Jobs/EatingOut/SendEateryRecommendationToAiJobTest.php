<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\EatingOut;

use App\Actions\EatingOut\SendEateryRecommendationToAiAction;
use App\DataObjects\EatingOut\AiPreparedRecommendation;
use App\Jobs\EatingOut\SendEateryRecommendationToAiJob;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryRecommendationAiData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendEateryRecommendationToAiJobTest extends TestCase
{
    protected EateryRecommendation $recommendation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recommendation = $this->create(EateryRecommendation::class, [
            'completed' => false,
            'ignored' => false,
            'email' => 'user@example.com',
        ]);
    }

    #[Test]
    public function itCallsTheActionWhenEligible(): void
    {
        $this->expectAction(SendEateryRecommendationToAiAction::class, return: $this->validDto());

        (new SendEateryRecommendationToAiJob($this->recommendation))->handle();
    }

    #[Test]
    public function itDoesNotCallTheActionWhenRecommendationIsCompleted(): void
    {
        $this->recommendation->completed = true;

        $this->dontExpectAction(SendEateryRecommendationToAiAction::class);

        (new SendEateryRecommendationToAiJob($this->recommendation))->handle();
    }

    #[Test]
    public function itDoesNotCallTheActionWhenRecommendationIsIgnored(): void
    {
        $this->recommendation->ignored = true;

        $this->dontExpectAction(SendEateryRecommendationToAiAction::class);

        (new SendEateryRecommendationToAiJob($this->recommendation))->handle();
    }

    #[Test]
    public function itDoesNotCallTheActionForBlockedEmail(): void
    {
        $recommendation = $this->create(EateryRecommendation::class, [
            'email' => 'alisondwheatley@gmail.com',
            'completed' => false,
            'ignored' => false,
        ]);

        $this->dontExpectAction(SendEateryRecommendationToAiAction::class);

        (new SendEateryRecommendationToAiJob($recommendation))->handle();
    }

    #[Test]
    public function itCreatesAiDataWhenEligible(): void
    {
        $this->assertDatabaseEmpty(EateryRecommendationAiData::class);

        $this->expectAction(SendEateryRecommendationToAiAction::class, return: $this->validDto());

        (new SendEateryRecommendationToAiJob($this->recommendation))->handle();

        $this->assertDatabaseCount(EateryRecommendationAiData::class, 1);
    }

    #[Test]
    public function itDoesNotCreateAiDataWhenNotEligible(): void
    {
        $this->recommendation->completed = true;

        $this->dontExpectAction(SendEateryRecommendationToAiAction::class);

        (new SendEateryRecommendationToAiJob($this->recommendation))->handle();

        $this->assertDatabaseEmpty(EateryRecommendationAiData::class);
    }

    #[Test]
    public function itMapsAllDtoFieldsOntoTheAiDataRecord(): void
    {
        $dto = $this->validDto();

        $this->expectAction(SendEateryRecommendationToAiAction::class, return: $dto);

        (new SendEateryRecommendationToAiJob($this->recommendation))->handle();

        $aiData = $this->recommendation->aiData()->first();

        $this->assertNotNull($aiData);
        $this->assertSame($dto->placeName, $aiData->place_name);
        $this->assertSame($dto->placeAddress, $aiData->place_address);
        $this->assertSame($dto->placeCountry, $aiData->place_country);
        $this->assertSame($dto->placeCounty, $aiData->place_county);
        $this->assertSame($dto->placeTown, $aiData->place_town);
        $this->assertSame($dto->placeArea, $aiData->place_area);
        $this->assertSame($dto->latitude, (float) $aiData->latitude);
        $this->assertSame($dto->longitude, (float) $aiData->longitude);
        $this->assertSame($dto->phoneNumber, $aiData->phone_number);
        $this->assertSame($dto->website, $aiData->website);
        $this->assertSame($dto->facebook, $aiData->facebook);
        $this->assertSame($dto->instagram, $aiData->instagram);
        $this->assertSame($dto->eateryType, $aiData->eatery_type);
        $this->assertSame($dto->venueType, $aiData->venue_type);
        $this->assertSame($dto->cuisine, $aiData->cuisine);
        $this->assertSame($dto->info, $aiData->info);
        $this->assertSame($dto->features, $aiData->features);
        $this->assertSame($dto->explanation, $aiData->explanation);
        $this->assertSame($dto->isEligible, $aiData->is_eligible);
    }

    protected function validDto(): AiPreparedRecommendation
    {
        return new AiPreparedRecommendation(
            placeName: 'Beach Box Cafe',
            placeAddress: "1 Ocean Drive\nPadstow\nCornwall\nPL28 8SB",
            placeCountry: 'England',
            placeCounty: 'Cornwall',
            placeTown: 'Padstow',
            placeArea: null,
            latitude: 50.5401661,
            longitude: -4.9935793,
            phoneNumber: '01208 640564',
            website: 'https://example.com',
            facebook: 'https://facebook.com/example',
            instagram: 'https://instagram.com/example',
            eateryType: 'Eatery',
            venueType: 'Cafe',
            cuisine: 'English',
            info: 'A lovely beachside cafe with gluten free options.',
            features: ['Gluten Free Menu', 'Parking'],
            explanation: 'Based on the official website.',
            isEligible: true,
        );
    }
}
