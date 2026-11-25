<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Pages;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\EatingOut\Eateries\Pages\CreateEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryFeature;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
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
