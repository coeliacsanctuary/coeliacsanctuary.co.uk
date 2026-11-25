<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryCounties\RelationManagers;

use App\Filament\Resources\EatingOut\EateryCounties\Pages\EditEateryCounty;
use App\Filament\Resources\EatingOut\EateryCounties\Pages\ViewEateryCounty;
use App\Filament\Resources\EatingOut\EateryCounties\RelationManagers\TownsRelationManager;
use App\Filament\Resources\EatingOut\EateryTowns\EateryTownResource;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TownsRelationManagerTest extends TestCase
{
    protected EateryCounty $county;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->county = $this->create(EateryCounty::class, ['county' => 'Cornwall', 'slug' => 'cornwall', 'legacy' => 'Old Cornwall', 'latlng' => '50.5,-4.6']);
    }

    #[Test]
    #[DataProvider('pages')]
    public function itIsShownOnThePage(string $page): void
    {
        Livewire::test($page, ['record' => $this->county->slug])->assertSeeLivewire(TownsRelationManager::class);
    }

    #[Test]
    #[DataProvider('pages')]
    public function itCanAddAndEditTownsFromThePage(string $page): void
    {
        $town = $this->create(EateryTown::class, ['county_id' => $this->county->id]);

        $this->townsPanel($this->county, $page)
            ->assertActionVisible(TestAction::make('create')->table())
            ->assertActionVisible(TestAction::make('edit')->table($town));
    }

    public static function pages(): array
    {
        return [
            'view' => [ViewEateryCounty::class],
            'edit' => [EditEateryCounty::class],
        ];
    }

    #[Test]
    public function itOnlyListsTownsInTheCountyIncludingThoseWithoutPlaces(): void
    {
        $mine = $this->create(EateryTown::class, ['county_id' => $this->county->id]);
        $theirs = EateryTown::query()->withoutGlobalScopes()->findOrFail(1);

        $this->townsPanel($this->county)
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs]);
    }

    #[Test]
    public function itCallsThemTowns(): void
    {
        $this->assertLabels($this->county, 'Town');
    }

    #[Test]
    public function itIsNotShownForLondon(): void
    {
        $london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london']);

        $this->assertFalse(TownsRelationManager::canViewForRecord($london, ViewEateryCounty::class));
    }

    #[Test]
    public function itPrefillsTheCountyWhenAddingATown(): void
    {
        $this->townsPanel($this->county)->assertActionHasUrl(
            TestAction::make('create')->table(),
            EateryTownResource::getUrl('create', ['county' => $this->county->id]),
        );
    }

    protected function assertLabels(EateryCounty $county, string $label): void
    {
        $table = $this->townsPanel($county)->instance()->getTable();

        $this->assertSame("{$label}s", TownsRelationManager::getTitle($county, ViewEateryCounty::class));
        $this->assertSame("{$label}s", $table->getHeading());
        $this->assertSame($label, $table->getColumn('town')->getLabel());
        $this->assertSame(mb_strtolower($label), $table->getModelLabel());
        $this->assertSame(mb_strtolower("{$label}s"), $table->getPluralModelLabel());
    }

    protected function townsPanel(EateryCounty $county, string $page = ViewEateryCounty::class): Testable
    {
        return Livewire::test(TownsRelationManager::class, [
            'ownerRecord' => $county,
            'pageClass' => $page,
        ]);
    }
}
