<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryAreas\Pages;

use App\Filament\Resources\EatingOut\EateryAreas\Pages\CreateEateryArea;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateEateryAreaTest extends TestCase
{
    protected EateryCounty $london;

    protected EateryTown $borough;

    protected EateryArea $area;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london', 'country_id' => 1]);
        $this->borough = $this->create(EateryTown::class, ['town' => 'Camden', 'slug' => 'camden', 'county_id' => $this->london->id]);
        $this->area = $this->create(EateryArea::class, ['area' => 'Holborn', 'slug' => 'holborn', 'latlng' => '51.5,-0.1', 'town_id' => $this->borough->id]);

        Storage::fake('media');
    }

    #[Test]
    public function itCreatesTheAreaWithoutAnImage(): void
    {
        $this->createArea()->assertHasNoFormErrors()->assertNotified()->assertRedirect();

        $area = $this->createdArea();

        $this->assertSame('Soho', $area->area);
        $this->assertSame($this->borough->id, $area->town_id);
        $this->assertSame('51.5,-0.2', $area->latlng);
        $this->assertNull($area->getFirstMedia('primary'));
    }

    #[Test]
    public function itStoresTheImage(): void
    {
        $this->createArea(['primary' => UploadedFile::fake()->image('area.jpg')])->assertHasNoFormErrors();

        $this->assertNotNull($this->createdArea()->getFirstMedia('primary'));
    }

    #[Test]
    public function itFillsTheSlugFromTheName(): void
    {
        Livewire::test(CreateEateryArea::class)
            ->set('data.area', 'Isle of Dogs')
            ->assertSet('data.slug', 'isle-of-dogs');
    }

    #[Test]
    public function itDoesNotOverwriteASlugThatHasBeenChanged(): void
    {
        Livewire::test(CreateEateryArea::class)
            ->set('data.area', 'Isle of Dogs')
            ->set('data.slug', 'custom')
            ->set('data.area', 'Newport')
            ->assertSet('data.slug', 'custom');
    }

    #[Test]
    public function itErrorsIfTheSlugIsUsedInTheSameBorough(): void
    {
        $this->create(EateryArea::class, ['slug' => 'soho', 'town_id' => $this->borough->id]);

        $this->createArea()->assertHasFormErrors(['slug' => 'unique']);
    }

    #[Test]
    public function itAllowsASlugUsedInAnotherBorough(): void
    {
        $this->create(EateryArea::class, [
            'slug' => 'soho',
            'town_id' => $this->create(EateryTown::class, ['county_id' => $this->london->id])->id,
        ]);

        $this->createArea()->assertHasNoFormErrors();
    }

    #[Test]
    public function itErrorsWithoutTheRequiredFields(): void
    {
        $this->createArea(['area' => null, 'slug' => null, 'town_id' => null])
            ->assertHasFormErrors(['area' => 'required', 'slug' => 'required', 'town_id' => 'required']);
    }

    #[Test]
    public function itErrorsIfTheNameOrSlugAreTooLong(): void
    {
        $this->createArea(['area' => str_repeat('a', 201), 'slug' => str_repeat('a', 201)])
            ->assertHasFormErrors(['area' => 'max', 'slug' => 'max']);
    }

    #[Test]
    public function itOffersEveryLondonBoroughIncludingThoseWithoutPlaces(): void
    {
        $barnet = $this->create(EateryTown::class, ['town' => 'Barnet', 'county_id' => $this->london->id]);

        Livewire::test(CreateEateryArea::class)->assertFormFieldExists(
            'town_id',
            fn (Select $field): bool => $field->getOptions() === [$barnet->id => 'Barnet', $this->borough->id => 'Camden'],
        );
    }

    #[Test]
    public function itErrorsIfTheTownIsNotInLondon(): void
    {
        $this->createArea(['town_id' => 1])->assertHasFormErrors(['town_id']);
    }

    #[Test]
    public function itPrefillsTheBoroughFromTheUrl(): void
    {
        Livewire::withQueryParams(['borough' => $this->borough->id])
            ->test(CreateEateryArea::class)
            ->assertSet('data.town_id', $this->borough->id);
    }

    /** @param array<string, mixed> $data */
    protected function createArea(array $data = []): Testable
    {
        return Livewire::test(CreateEateryArea::class)
            ->fillForm([
                'area' => 'Soho',
                'slug' => 'soho',
                'town_id' => $this->borough->id,
                'latlng' => '51.5,-0.2',
                ...$data,
            ])
            ->call('create');
    }

    protected function createdArea(): EateryArea
    {
        return EateryArea::query()->withoutGlobalScopes()->where('slug', 'soho')->firstOrFail();
    }
}
