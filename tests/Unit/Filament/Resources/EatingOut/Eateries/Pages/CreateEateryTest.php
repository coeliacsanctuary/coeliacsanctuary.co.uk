<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Pages;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\EatingOut\Eateries\Pages\CreateEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryRecommendationAiData;
use App\Models\EatingOut\EateryVenueType;
use App\Models\User;
use App\Notifications\EatingOut\EateryRecommendationAddedNotification;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateEateryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->create(EateryCounty::class, ['id' => 2, 'county' => 'Cornwall', 'country_id' => 1]);
        $this->create(EateryTown::class, ['id' => 2, 'town' => 'Truro', 'county_id' => 2]);

        $this->create(Eatery::class);
    }

    #[Test]
    public function itCreatesTheEatery(): void
    {
        $this->assertDatabaseCount(Eatery::class, 1);

        $this->createEatery()->assertNotified();

        $this->assertDatabaseCount(Eatery::class, 2);

        $eatery = $this->createdEatery();

        $this->assertSame('The Gluten Free Cafe', $eatery->name);
        $this->assertSame('A lovely little cafe.', $eatery->info);
        $this->assertTrue($eatery->live);
    }

    #[Test]
    public function itGeneratesTheSlug(): void
    {
        $this->createEatery();

        $this->assertSame('the-gluten-free-cafe', $this->createdEatery()->slug);
    }

    #[Test]
    public function itStoresTheSnippet(): void
    {
        $this->createEatery(['snippet' => 'A lovely little cafe in Crewe.']);

        $this->assertSame('A lovely little cafe in Crewe.', $this->createdEatery()->snippet);
    }

    #[Test]
    public function itResolvesTheLocationFromTheLookup(): void
    {
        $this->createEatery();

        $eatery = $this->createdEatery();

        $this->assertSame(1, $eatery->country_id);
        $this->assertSame(2, $eatery->county_id);
        $this->assertSame(2, $eatery->town_id);
    }

    #[Test]
    public function itStoresTheContactDetails(): void
    {
        $this->createEatery([
            'phone' => '01270 123456',
            'website' => 'https://example.com',
            'gf_menu_link' => 'https://example.com/gf',
            'facebook_url' => 'https://facebook.com/example',
            'instagram_url' => 'https://instagram.com/example',
        ]);

        $eatery = $this->createdEatery();

        $this->assertSame('01270 123456', $eatery->phone);
        $this->assertSame('https://example.com', $eatery->website);
        $this->assertSame('https://example.com/gf', $eatery->gf_menu_link);
        $this->assertSame('https://facebook.com/example', $eatery->facebook_url);
        $this->assertSame('https://instagram.com/example', $eatery->instagram_url);
    }

    #[Test]
    public function itAttachesTheSelectedFeatures(): void
    {
        $features = EateryFeature::query()->take(2)->pluck('id');

        $this->createEatery(['features' => $features->all()]);

        $this->assertSame(2, $this->createdEatery()->features()->count());
    }

    #[Test]
    public function itSendsTheUserBackToTheEateryListAfterCreating(): void
    {
        $this->createEatery()->assertRedirect(EateryResource::getUrl('index'));
    }

    #[Test]
    public function itDefaultsTheTypeToEatery(): void
    {
        Livewire::test(CreateEatery::class)->assertSchemaComponentStateSet('type_id', 1);
    }

    #[Test]
    public function itPrefillsTheLocationFromATownWithoutPlacesAndKeepsTheDefaults(): void
    {
        Livewire::withQueryParams(['town' => 2])->test(CreateEatery::class)
            ->assertSchemaComponentStateSet('country_id', 1)
            ->assertSchemaComponentStateSet('county_id', 2)
            ->assertSchemaComponentStateSet('town_id', 2)
            ->assertSchemaComponentStateSet('type_id', 1);
    }

    #[Test]
    public function itPrefillsTheLocationFromAnAreaWithoutPlacesAndKeepsTheDefaults(): void
    {
        $london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london', 'country_id' => 1]);
        $borough = $this->create(EateryTown::class, ['county_id' => $london->id]);
        $area = $this->create(EateryArea::class, ['town_id' => $borough->id]);

        Livewire::withQueryParams(['area' => $area->id])->test(CreateEatery::class)
            ->assertSchemaComponentStateSet('country_id', 1)
            ->assertSchemaComponentStateSet('county_id', $london->id)
            ->assertSchemaComponentStateSet('town_id', $borough->id)
            ->assertSchemaComponentStateSet('area_id', $area->id)
            ->assertSchemaComponentStateSet('type_id', 1);
    }

    #[Test]
    public function itPrefillsTheCuisineAndKeepsTheDefaults(): void
    {
        Livewire::withQueryParams(['cuisine' => 2])->test(CreateEatery::class)
            ->assertSchemaComponentStateSet('cuisine_id', 2)
            ->assertSchemaComponentStateSet('type_id', 1);
    }

    #[Test]
    public function itStoresTheOpeningTimes(): void
    {
        $this->createEatery([
            'no_opening_times' => false,
            'openingTimes.monday_start_h' => 8,
            'openingTimes.monday_start_m' => 15,
            'openingTimes.monday_end_h' => 20,
            'openingTimes.monday_end_m' => 30,
            'openingTimes.tuesday_closed' => true,
        ]);

        $openingTimes = $this->createdEatery()->openingTimes;

        $this->assertSame('08:15:00', $openingTimes->monday_start);
        $this->assertSame('20:30:00', $openingTimes->monday_end);
        $this->assertNull($openingTimes->tuesday_start);
        $this->assertNull($openingTimes->tuesday_end);
    }

    #[Test]
    public function itDoesNotStoreOpeningTimesWhenTheyAreUnknown(): void
    {
        $this->createEatery();

        $this->assertNull($this->createdEatery()->openingTimes);
    }

    #[Test]
    public function itPrefillsFromARecommendationWithoutAiData(): void
    {
        $recommendation = $this->create(EateryRecommendation::class, [
            'place_name' => 'The Recommended Cafe',
            'place_location' => '1 Market Street, Crewe',
            'place_web_address' => 'https://recommended.example.com',
            'place_venue_type_id' => 2,
            'place_details' => 'Great gluten free cakes.',
        ]);

        Livewire::withQueryParams(['recommendation' => $recommendation->id])->test(CreateEatery::class)
            ->assertSchemaComponentStateSet('name', 'The Recommended Cafe')
            ->assertSchemaComponentStateSet('address', '1 Market Street, Crewe')
            ->assertSchemaComponentStateSet('website', 'https://recommended.example.com')
            ->assertSchemaComponentStateSet('venue_type_id', 2)
            ->assertSchemaComponentStateSet('info', 'Great gluten free cakes.')
            ->assertSchemaComponentStateSet('type_id', 1)
            ->assertSchemaComponentHidden('recommendation_eligibility');
    }

    #[Test]
    public function itPrefillsFromARecommendationsAiData(): void
    {
        $recommendation = $this->create(EateryRecommendation::class, ['place_details' => 'Great gluten free cakes.']);

        $venueType = EateryVenueType::query()->findOrFail(3);
        $cuisine = EateryCuisine::query()->findOrFail(2);
        $feature = EateryFeature::query()->firstOrFail();

        $this->build(EateryRecommendationAiData::class)->on($recommendation)->create([
            'place_name' => 'The AI Cafe',
            'place_address' => '2 High Street, Truro',
            'place_country' => 'England',
            'place_county' => 'Cornwall',
            'place_town' => 'Truro',
            'latitude' => '50.26',
            'longitude' => '-5.05',
            'phone_number' => '01872 123456',
            'website' => 'https://ai.example.com',
            'facebook' => 'https://facebook.com/ai',
            'instagram' => 'https://instagram.com/ai',
            'eatery_type' => 'Hotel',
            'venue_type' => $venueType->venue_type,
            'cuisine' => $cuisine->cuisine,
            'info' => 'An AI summary.',
            'features' => [$feature->feature],
            'completed_at' => now(),
        ]);

        Livewire::withQueryParams(['recommendation' => $recommendation->id])->test(CreateEatery::class)
            ->assertSchemaComponentStateSet('name', 'The AI Cafe')
            ->assertSchemaComponentStateSet('address', '2 High Street, Truro')
            ->assertSchemaComponentStateSet('country_id', 1)
            ->assertSchemaComponentStateSet('county_id', 2)
            ->assertSchemaComponentStateSet('town_id', 2)
            ->assertSchemaComponentStateSet('lat', '50.26')
            ->assertSchemaComponentStateSet('lng', '-5.05')
            ->assertSchemaComponentStateSet('map', ['lat' => 50.26, 'lng' => -5.05])
            ->assertSchemaComponentStateSet('phone', '01872 123456')
            ->assertSchemaComponentStateSet('website', 'https://ai.example.com')
            ->assertSchemaComponentStateSet('facebook_url', 'https://facebook.com/ai')
            ->assertSchemaComponentStateSet('instagram_url', 'https://instagram.com/ai')
            ->assertSchemaComponentStateSet('type_id', 3)
            ->assertSchemaComponentStateSet('venue_type_id', 3)
            ->assertSchemaComponentStateSet('cuisine_id', 2)
            ->assertSchemaComponentStateSet('info', "An AI summary.\n\nSubmitted Data:\n\nGreat gluten free cakes.")
            ->assertSchemaComponentStateSet('features', [$feature->id]);
    }

    #[Test]
    public function itShowsTheAiEligibilityForARecommendation(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        $this->build(EateryRecommendationAiData::class)->on($recommendation)->create(['completed_at' => now()]);

        Livewire::withQueryParams(['recommendation' => $recommendation->id])->test(CreateEatery::class)
            ->assertSchemaComponentVisible('recommendation_eligibility');
    }

    #[Test]
    public function itDoesNotShowTheAiEligibilityWhileTheAiCheckIsInProgress(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        $this->build(EateryRecommendationAiData::class)->on($recommendation)->create(['completed_at' => null]);

        Livewire::withQueryParams(['recommendation' => $recommendation->id])->test(CreateEatery::class)
            ->assertSchemaComponentHidden('recommendation_eligibility');
    }

    #[Test]
    public function itCompletesTheRecommendationAndEmailsTheUserAfterCreating(): void
    {
        Notification::fake();

        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::withQueryParams(['recommendation' => $recommendation->id])->test(CreateEatery::class)
            ->fillForm([
                'name' => 'The Gluten Free Cafe',
                'location' => '1|2|2|',
                'address' => "1 High Street\nCrewe\nCW1 2AB",
                'lat' => 53.0977,
                'lng' => -2.4426,
                'type_id' => 1,
                'venue_type_id' => 1,
                'cuisine_id' => 1,
                'info' => 'A lovely little cafe.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue($recommendation->refresh()->completed);

        Notification::assertSentTo(new AnonymousNotifiable(), EateryRecommendationAddedNotification::class);
    }

    #[Test]
    public function itDoesNotSendTheRecommendationEmailWhenNotCreatedFromARecommendation(): void
    {
        Notification::fake();

        $this->createEatery();

        Notification::assertNothingSent();
    }

    protected function createEatery(array $overrides = []): Testable
    {
        return Livewire::test(CreateEatery::class)
            ->fillForm([
                'name' => 'The Gluten Free Cafe',
                'live' => true,
                'location' => '1|2|2|',
                'address' => "1 High Street\nCrewe\nCW1 2AB",
                'lat' => 53.0977,
                'lng' => -2.4426,
                'type_id' => 1,
                'venue_type_id' => 1,
                'cuisine_id' => 1,
                'info' => 'A lovely little cafe.',
                ...$overrides,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    protected function createdEatery(): Eatery
    {
        return Eatery::query()->withoutGlobalScopes()->latest('id')->firstOrFail();
    }
}
