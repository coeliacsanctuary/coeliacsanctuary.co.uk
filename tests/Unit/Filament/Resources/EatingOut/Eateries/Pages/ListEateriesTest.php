<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Pages;

use App\Filament\Resources\EatingOut\Eateries\Pages\ListEateries;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListEateriesTest extends TestCase
{
    protected Eatery $eatery;

    protected Eatery $attraction;

    protected Eatery $hotel;

    protected Eatery $chain;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->create(EateryCounty::class, ['id' => 2, 'county' => 'Cornwall', 'country_id' => 1]);
        $this->create(EateryTown::class, ['id' => 2, 'town' => 'Truro', 'county_id' => 2]);

        $this->eatery = $this->create(Eatery::class, ['county_id' => 2, 'town_id' => 2]);
        $this->attraction = $this->build(Eatery::class)->attraction()->create(['county_id' => 2, 'town_id' => 2]);
        $this->hotel = $this->build(Eatery::class)->hotel()->create(['county_id' => 2, 'town_id' => 2]);
        $this->chain = $this->create(Eatery::class, ['county_id' => 1, 'town_id' => 1]);
    }

    #[Test]
    public function itRegistersEveryTab(): void
    {
        $this->assertSame(
            ['all', 'eateries', 'attractions', 'hotels', 'chains'],
            array_keys(Livewire::test(ListEateries::class)->instance()->getTabs()),
        );
    }

    #[Test]
    public function itShowsEverythingOnTheAllTab(): void
    {
        $this->listEateries('all')
            ->assertCanSeeTableRecords([$this->eatery, $this->attraction, $this->hotel, $this->chain]);
    }

    #[Test]
    public function itOnlyShowsEateriesOnTheEateriesTab(): void
    {
        $this->listEateries('eateries')
            ->assertCanSeeTableRecords([$this->eatery])
            ->assertCanNotSeeTableRecords([$this->attraction, $this->hotel, $this->chain]);
    }

    #[Test]
    public function itOnlyShowsAttractionsOnTheAttractionsTab(): void
    {
        $this->listEateries('attractions')
            ->assertCanSeeTableRecords([$this->attraction])
            ->assertCanNotSeeTableRecords([$this->eatery, $this->hotel, $this->chain]);
    }

    #[Test]
    public function itOnlyShowsHotelsOnTheHotelsTab(): void
    {
        $this->listEateries('hotels')
            ->assertCanSeeTableRecords([$this->hotel])
            ->assertCanNotSeeTableRecords([$this->eatery, $this->attraction, $this->chain]);
    }

    #[Test]
    public function itOnlyShowsChainsOnTheChainsTab(): void
    {
        $this->listEateries('chains')
            ->assertCanSeeTableRecords([$this->chain])
            ->assertCanNotSeeTableRecords([$this->eatery, $this->attraction, $this->hotel]);
    }

    protected function listEateries(string $tab): Testable
    {
        return Livewire::test(ListEateries::class)
            ->set('activeTab', $tab)
            ->assertOk();
    }
}
