<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\SendEateryRecommendationToAiAction;
use App\Ai\Agents\PrepareRecommendedEatery;
use App\DataObjects\EatingOut\AiPreparedRecommendation;
use App\Models\EatingOut\EateryRecommendation;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendEateryRecommendationToAiActionTest extends TestCase
{
    protected EateryRecommendation $recommendation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recommendation = $this->create(EateryRecommendation::class);

        PrepareRecommendedEatery::fake();
    }

    #[Test]
    public function itPromptsThePrepareRecommendedEateryAgent(): void
    {
        PrepareRecommendedEatery::fake([$this->validFakeResponse()]);

        app(SendEateryRecommendationToAiAction::class)->handle($this->recommendation);

        PrepareRecommendedEatery::assertPrompted(
            fn ($prompt) => str_contains($prompt->prompt, 'Place Name: ' . $this->recommendation->place_name)
        );
    }

    #[Test]
    public function itReturnsAnAiPreparedRecommendationDataObject(): void
    {
        PrepareRecommendedEatery::fake([$this->validFakeResponse()]);

        $result = app(SendEateryRecommendationToAiAction::class)->handle($this->recommendation);

        $this->assertInstanceOf(AiPreparedRecommendation::class, $result);
    }

    #[Test]
    public function itMapsTheAgentResponseDataOntoTheDataObject(): void
    {
        PrepareRecommendedEatery::fake([[
            'data' => [
                'place_name' => 'Beach Box Cafe',
                'place_address' => '1 Ocean Drive',
                'place_country' => 'England',
                'place_county' => 'Cornwall',
                'place_town' => 'Padstow',
                'place_area' => 'North Cornwall',
                'latitude' => 50.5401661,
                'longitude' => -4.9935793,
                'phone_number' => '01208 640564',
                'website' => 'https://example.com',
                'facebook' => 'https://facebook.com/example',
                'instagram' => 'https://instagram.com/example',
                'eatery_Type' => 'Eatery',
                'venue_type' => 'Cafe',
                'cuisine' => 'English',
                'info' => 'A lovely beachside cafe.',
                'features' => ['Gluten Free Menu', 'Parking'],
            ],
            'explanation' => 'Based on the website.',
            'is_eligible' => true,
        ]]);

        $result = app(SendEateryRecommendationToAiAction::class)->handle($this->recommendation);

        $this->assertSame('Beach Box Cafe', $result->placeName);
        $this->assertSame('Cornwall', $result->placeCounty);
        $this->assertSame(50.5401661, $result->latitude);
        $this->assertSame(['Gluten Free Menu', 'Parking'], $result->features);
        $this->assertSame('Based on the website.', $result->explanation);
        $this->assertTrue($result->isEligible);
    }

    #[Test]
    public function itHandlesNullableFieldsInTheResponse(): void
    {
        PrepareRecommendedEatery::fake([[
            'data' => [
                'place_name' => null,
                'place_address' => null,
                'place_country' => null,
                'place_county' => null,
                'place_town' => null,
                'place_area' => null,
                'latitude' => null,
                'longitude' => null,
                'phone_number' => null,
                'website' => null,
                'facebook' => null,
                'instagram' => null,
                'eatery_Type' => null,
                'venue_type' => null,
                'cuisine' => null,
                'info' => null,
                'features' => null,
            ],
            'explanation' => 'Could not resolve details.',
            'is_eligible' => false,
        ]]);

        $result = app(SendEateryRecommendationToAiAction::class)->handle($this->recommendation);

        $this->assertNull($result->placeName);
        $this->assertNull($result->latitude);
        $this->assertNull($result->features);
        $this->assertFalse($result->isEligible);
    }

    #[Test]
    public function itSetsIsEligibleFromTheAgentResponse(): void
    {
        PrepareRecommendedEatery::fake([$this->validFakeResponse(isEligible: false)]);

        $result = app(SendEateryRecommendationToAiAction::class)->handle($this->recommendation);

        $this->assertFalse($result->isEligible);
    }

    /** @return array<string, mixed> */
    protected function validFakeResponse(bool $isEligible = true): array
    {
        return [
            'data' => [
                'place_name' => 'Test Cafe',
                'place_address' => '1 Test Street',
                'place_country' => 'England',
                'place_county' => 'Test County',
                'place_town' => 'Test Town',
                'place_area' => null,
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'phone_number' => '01234 567890',
                'website' => 'https://testcafe.example.com',
                'facebook' => null,
                'instagram' => null,
                'eatery_Type' => 'Eatery',
                'venue_type' => 'Cafe',
                'cuisine' => 'English',
                'info' => 'A test cafe with gluten free options.',
                'features' => ['Gluten Free Menu'],
            ],
            'explanation' => 'Test explanation.',
            'is_eligible' => $isEligible,
        ];
    }
}
