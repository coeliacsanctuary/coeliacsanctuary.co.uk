<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Schemas\Components;

use App\DataObjects\EatingOut\LatLng;
use App\Filament\Resources\EatingOut\Eateries\Pages\CreateEatery;
use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Filament\Resources\EatingOut\NationwideBranches\Pages\CreateNationwideBranch;
use App\Filament\Support\FindEateryLocation;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use App\Services\EatingOut\LocationSearchService;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryLocationSectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->create(EateryCounty::class, ['id' => 2, 'county' => 'Cornwall', 'slug' => 'cornwall']);
        $this->create(EateryTown::class, ['id' => 2, 'town' => 'Truro', 'slug' => 'truro', 'county_id' => 2]);
        $this->create(EateryCounty::class, ['id' => 3, 'county' => 'London', 'slug' => 'london']);
        $this->create(EateryTown::class, ['id' => 3, 'town' => 'Hackney', 'slug' => 'hackney', 'county_id' => 3]);
        $this->create(EateryArea::class, ['id' => 3, 'area' => 'Shoreditch', 'slug' => 'shoreditch', 'town_id' => 3]);
        $this->create(EateryTown::class, ['id' => 529, 'town' => 'Nationwide', 'slug' => 'nationwide', 'county_id' => 1]);
    }

    #[Test]
    #[DataProvider('searches')]
    public function itLabelsTheSearchResults(string $term, string $key, string $expected): void
    {
        $results = app(FindEateryLocation::class)->handle($term);

        $this->assertArrayHasKey($key, $results);
        $this->assertStringContainsString($expected, $results[$key]);
    }

    public static function searches(): array
    {
        return [
            'county' => ['Cornwall', '1|2||', '<strong>County</strong> - Cornwall, England'],
            'town' => ['Truro', '1|2|2|', '<strong>Town</strong> - Truro, Cornwall, England'],
            'borough' => ['Hackney', '1|3|3|', '<strong>London Borough</strong> - Hackney, London, England'],
            'area' => ['Shoreditch', '1|3|3|3', '<strong>London Area</strong> - Shoreditch, Hackney, London, England'],
        ];
    }

    #[Test]
    public function itEscapesTheSearchResults(): void
    {
        $this->create(EateryTown::class, ['id' => 4, 'town' => 'Bad <b>Town</b>', 'county_id' => 2]);

        $this->assertStringNotContainsString('<b>', app(FindEateryLocation::class)->handle('Bad')['1|2|4|']);
    }

    #[Test]
    public function itFillsTheLocationFromTheSearchAndEmptiesTheSearchBox(): void
    {
        $this->createPage()
            ->set('data.location', '1|3|3|3')
            ->assertSet('data.country_id', 1)
            ->assertSet('data.county_id', 3)
            ->assertSet('data.town_id', 3)
            ->assertSet('data.area_id', 3)
            ->assertSet('data.location', null);
    }

    #[Test]
    public function itLeavesTheTownEmptyForACountyResult(): void
    {
        $this->createPage()
            ->set('data.location', '1|2||')
            ->assertSet('data.county_id', 2)
            ->assertSet('data.town_id', null)
            ->assertSet('data.area_id', null);
    }

    #[Test]
    public function itKeepsTheLocationWhenTheSearchIsCleared(): void
    {
        $this->createPage()
            ->set('data.location', '1|2|2|')
            ->set('data.location')
            ->assertSet('data.county_id', 2)
            ->assertSet('data.town_id', 2);
    }

    #[Test]
    public function itClearsTheChildrenWhenAParentChanges(): void
    {
        $this->createPage()
            ->set('data.location', '1|3|3|3')
            ->set('data.county_id', 2)
            ->assertSet('data.town_id', null)
            ->assertSet('data.area_id', null);
    }

    #[Test]
    public function itOnlyShowsTheAreaForLondon(): void
    {
        $this->createPage()
            ->set('data.location', '1|2|2|')
            ->assertSchemaComponentHidden('area_id')
            ->set('data.location', '1|3|3|')
            ->assertSchemaComponentVisible('area_id');
    }

    #[Test]
    public function itRequiresATown(): void
    {
        $this->createPage()
            ->fillForm([...$this->eateryData(), 'location' => '1|2||'])
            ->call('create')
            ->assertHasFormErrors(['town_id' => 'required']);
    }

    #[Test]
    public function itAddsATownAndSelectsIt(): void
    {
        $this->mock(LocationSearchService::class)
            ->shouldReceive('getLatLng')
            ->andReturn(new LatLng(50.1, -5.2));

        $page = $this->addPlace('1|2||', 'town_id', ['town' => 'Bodmin', 'slug' => 'bodmin'])->assertHasNoFormErrors();

        $town = EateryTown::query()->withoutGlobalScopes()->where('slug', 'bodmin')->firstOrFail();

        $this->assertSame(2, $town->county_id);
        $this->assertSame('50.1,-5.2', $town->latlng);

        $page->assertSet('data.town_id', $town->id);
    }

    #[Test]
    public function itRejectsATownSlugUsedInTheSameCounty(): void
    {
        $this->addPlace('1|2||', 'town_id', ['town' => 'Truro', 'slug' => 'truro', 'latlng' => '1,1'])
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    #[Test]
    public function itAllowsATownSlugUsedInAnotherCounty(): void
    {
        $this->addPlace('1|3||', 'town_id', ['town' => 'Truro', 'slug' => 'truro', 'latlng' => '1,1'])
            ->assertHasNoFormErrors();

        $this->assertSame(2, EateryTown::query()->withoutGlobalScopes()->where('slug', 'truro')->count());
    }

    #[Test]
    public function itAddsAnAreaAndSelectsIt(): void
    {
        $page = $this->addPlace('1|3|3|', 'area_id', ['area' => 'Dalston', 'slug' => 'dalston', 'latlng' => '1,1'])
            ->assertHasNoFormErrors();

        $area = EateryArea::query()->withoutGlobalScopes()->where('slug', 'dalston')->firstOrFail();

        $this->assertSame(3, $area->town_id);

        $page->assertSet('data.area_id', $area->id);
    }

    #[Test]
    public function itRejectsAnAreaSlugUsedInTheSameBorough(): void
    {
        $this->addPlace('1|3|3|', 'area_id', ['area' => 'Shoreditch', 'slug' => 'shoreditch', 'latlng' => '1,1'])
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    #[Test]
    public function itAllowsAnAreaSlugUsedInAnotherBorough(): void
    {
        $this->create(EateryTown::class, ['id' => 5, 'town' => 'Islington', 'slug' => 'islington', 'county_id' => 3]);

        $this->addPlace('1|3|5|', 'area_id', ['area' => 'Shoreditch', 'slug' => 'shoreditch', 'latlng' => '1,1'])
            ->assertHasNoFormErrors();
    }

    #[Test]
    public function itAddsACountyAndSelectsIt(): void
    {
        $page = $this->addPlace('1|2|2|', 'county_id', ['county' => 'Devon', 'slug' => 'devon', 'latlng' => '1,1'])
            ->assertHasNoFormErrors();

        $county = EateryCounty::query()->withoutGlobalScopes()->where('slug', 'devon')->firstOrFail();

        $page->assertSet('data.county_id', $county->id)->assertSet('data.town_id', null);
    }

    #[Test]
    public function itCreatesAChainWithoutALocation(): void
    {
        $this->createPage()
            ->fillForm([...$this->eateryData(), 'is_nationwide' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $chain = Eatery::query()->withoutGlobalScopes()->latest('id')->firstOrFail();

        $this->assertSame(1, $chain->county_id);
        $this->assertSame(529, $chain->town_id);
        $this->assertNull($chain->area_id);
        $this->assertSame('', $chain->address);
        $this->assertSame(0.0, $chain->lat);
        $this->assertSame(0.0, $chain->lng);
    }

    #[Test]
    public function itClearsTheLocationWhenAChainIsTurnedOff(): void
    {
        $this->createPage()
            ->set('data.is_nationwide', true)
            ->assertSet('data.town_id', 529)
            ->set('data.is_nationwide', false)
            ->assertSet('data.county_id', null)
            ->assertSet('data.town_id', null);
    }

    #[Test]
    public function itSavesAnExistingChain(): void
    {
        $chain = $this->create(Eatery::class, ['county_id' => 1, 'town_id' => 529]);

        Livewire::test(EditEatery::class, ['record' => $chain->id])
            ->fillForm(['name' => 'New Chain Name'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('New Chain Name', $chain->refresh()->name);
        $this->assertSame(529, $chain->town_id);
    }

    #[Test]
    public function itMovesAnEateryToAnotherTown(): void
    {
        $this->create(EateryTown::class, ['id' => 6, 'town' => 'Bodmin', 'county_id' => 2]);

        $eatery = $this->create(Eatery::class, ['county_id' => 2, 'town_id' => 2]);

        Livewire::test(EditEatery::class, ['record' => $eatery->id])
            ->fillForm(['town_id' => 6])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(6, $eatery->refresh()->town_id);
    }

    #[Test]
    public function itFillsTheLocationOnTheBranchForm(): void
    {
        Livewire::test(CreateNationwideBranch::class)
            ->assertOk()
            ->set('data.location', '1|2|2|')
            ->assertSet('data.county_id', 2)
            ->assertSet('data.town_id', 2);
    }

    protected function createPage(): Testable
    {
        return Livewire::test(CreateEatery::class);
    }

    protected function addPlace(string $location, string $field, array $data): Testable
    {
        return $this->createPage()
            ->set('data.location', $location)
            ->callAction(TestAction::make('createOption')->schemaComponent($field), $data);
    }

    protected function eateryData(): array
    {
        return [
            'name' => 'The Gluten Free Cafe',
            'live' => true,
            'address' => '1 High Street',
            'lat' => 53.0977,
            'lng' => -2.4426,
            'type_id' => 1,
            'venue_type_id' => 1,
            'cuisine_id' => 1,
            'info' => 'A lovely little cafe.',
        ];
    }
}
