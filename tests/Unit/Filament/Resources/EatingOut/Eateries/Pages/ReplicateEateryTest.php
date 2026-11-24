<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Pages;

use App\Filament\Resources\EatingOut\Eateries\Pages\CreateEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryOpeningTimes;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReplicateEateryTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->create(EateryCounty::class, ['id' => 2, 'county' => 'Cornwall']);
        $this->create(EateryTown::class, ['id' => 2, 'town' => 'Truro', 'county_id' => 2]);

        $this->eatery = $this->create(Eatery::class, [
            'name' => 'The Gluten Free Cafe',
            'slug' => 'the-gluten-free-cafe',
            'county_id' => 2,
            'town_id' => 2,
            'address' => "1 High Street\nTR1 1AA",
        ]);
    }

    #[Test]
    public function itPrefillsTheFormWithoutSavingAnything(): void
    {
        $this->replicate()
            ->assertSet('data.name', 'The Gluten Free Cafe')
            ->assertSet('data.county_id', 2)
            ->assertSet('data.town_id', 2)
            ->assertSet('data.address', "1 High Street\nTR1 1AA")
            ->assertSet('data.live', true);

        $this->assertDatabaseCount(Eatery::class, 1);
    }

    #[Test]
    public function itCreatesANewEateryAndLeavesTheOriginalAlone(): void
    {
        $this->replicate()
            ->fillForm(['address' => "2 Low Street\nTR2 2BB"])
            ->call('create')
            ->assertHasNoFormErrors();

        $copy = $this->copy();

        $this->assertNotSame($this->eatery->id, $copy->id);
        $this->assertSame('the-gluten-free-cafe-tr2-2bb', $copy->slug);
        $this->assertSame("2 Low Street\nTR2 2BB", $copy->address);
        $this->assertSame('the-gluten-free-cafe', $this->eatery->refresh()->slug);
        $this->assertSame("1 High Street\nTR1 1AA", $this->eatery->address);
    }

    #[Test]
    public function itKeepsTheLiveValue(): void
    {
        $this->eatery->update(['live' => false]);

        $this->replicate()->call('create')->assertHasNoFormErrors();

        $this->assertFalse($this->copy()->live);
        $this->assertDatabaseCount(Eatery::class, 2);
    }

    #[Test]
    public function itCopiesTheFeatures(): void
    {
        $features = EateryFeature::query()->take(2)->pluck('id')->all();
        $this->eatery->features()->attach($features);

        $this->replicate()->call('create')->assertHasNoFormErrors();

        $this->assertEqualsCanonicalizing($features, $this->copy()->features->pluck('id')->all());
        $this->assertCount(2, $this->eatery->features()->get());
    }

    #[Test]
    public function itCopiesTheOpeningTimes(): void
    {
        $this->create(EateryOpeningTimes::class, [
            'wheretoeat_id' => $this->eatery->id,
            'monday_start' => '09:30:00',
            'monday_end' => '17:45:00',
            'sunday_start' => null,
            'sunday_end' => null,
        ]);

        $this->replicate()->call('create')->assertHasNoFormErrors();

        $openingTimes = $this->copy()->openingTimes;

        $this->assertSame('09:30:00', $openingTimes->monday_start);
        $this->assertSame('17:45:00', $openingTimes->monday_end);
        $this->assertNull($openingTimes->sunday_start);
        $this->assertDatabaseCount(EateryOpeningTimes::class, 2);
    }

    #[Test]
    public function itCopiesTheRestaurants(): void
    {
        $this->eatery->update(['type_id' => 2, 'info' => null, 'cuisine_id' => 29]);
        $this->create(EateryCuisine::class, ['id' => 29]);
        EateryVenueType::query()->whereKey($this->eatery->venue_type_id)->update(['type_id' => 2]);

        $this->create(EateryAttractionRestaurant::class, [
            'wheretoeat_id' => $this->eatery->id,
            'restaurant_name' => 'The Grill',
            'info' => 'Burgers',
        ]);

        $this->replicate()->call('create')->assertHasNoFormErrors();

        $this->assertSame('The Grill', $this->copy()->restaurants()->firstOrFail()->restaurant_name);
        $this->assertDatabaseCount(EateryAttractionRestaurant::class, 2);
        $this->assertCount(1, $this->eatery->restaurants()->get());
    }

    #[Test]
    public function itStartsACopyOfAClosedEateryOpen(): void
    {
        $this->eatery->update(['closed_down' => true]);

        $this->replicate()->call('create')->assertHasNoFormErrors();

        $this->assertFalse($this->copy()->closed_down);
    }

    #[Test]
    public function itReplicatesAChain(): void
    {
        $this->create(EateryTown::class, ['id' => 529, 'town' => 'Nationwide', 'county_id' => 1]);
        $this->eatery->update(['county_id' => 1, 'town_id' => 529]);

        $this->replicate()
            ->assertSet('data.is_nationwide', true)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount(Eatery::class, 2);
        $this->assertSame(529, $this->copy()->town_id);
    }

    #[Test]
    public function itReturnsNotFoundForAnUnknownEatery(): void
    {
        Livewire::withQueryParams(['replicate' => 999])->test(CreateEatery::class)->assertNotFound();
    }

    protected function replicate(): Testable
    {
        return Livewire::withQueryParams(['replicate' => $this->eatery->id])
            ->test(CreateEatery::class)
            ->assertOk();
    }

    protected function copy(): Eatery
    {
        return Eatery::query()->withoutGlobalScopes()->latest('id')->firstOrFail();
    }
}
