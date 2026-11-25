<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryBoroughs\RelationManagers;

use App\Filament\Resources\EatingOut\EateryBoroughs\Pages\EditEateryBorough;
use App\Filament\Resources\EatingOut\EateryBoroughs\RelationManagers\AreasRelationManager;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AreasRelationManagerTest extends TestCase
{
    protected EateryCounty $london;

    protected EateryTown $borough;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london']);

        $this->borough = $this->create(EateryTown::class, ['county_id' => $this->london->id]);
    }

    #[Test]
    public function itIsShownOnTheEditPage(): void
    {
        Livewire::withQueryParams(['relation' => '1'])
            ->test(EditEateryBorough::class, ['record' => $this->borough->id])
            ->assertSeeLivewire(AreasRelationManager::class);
    }

    #[Test]
    public function itOnlyListsAreasInTheBoroughIncludingThoseWithoutPlaces(): void
    {
        $mine = $this->create(EateryArea::class, ['town_id' => $this->borough->id]);
        $theirs = $this->create(EateryArea::class, ['town_id' => $this->create(EateryTown::class, ['county_id' => $this->london->id])->id]);

        $this->areasPanel()
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs]);
    }

    #[Test]
    public function itCanAddAnArea(): void
    {
        $this->areasPanel()->assertActionVisible(TestAction::make('create')->table());
    }

    protected function areasPanel(): Testable
    {
        return Livewire::test(AreasRelationManager::class, [
            'ownerRecord' => $this->borough,
            'pageClass' => EditEateryBorough::class,
        ]);
    }
}
