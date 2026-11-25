<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryTowns\Pages;

use App\Filament\Resources\EatingOut\EateryTowns\Pages\CreateEateryTown;
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

class CreateEateryTownTest extends TestCase
{
    protected EateryCounty $london;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london']);

        Storage::fake('media');
    }

    #[Test]
    public function itCreatesTheTownWithoutAnImage(): void
    {
        $this->createTown()->assertHasNoFormErrors()->assertNotified()->assertRedirect();

        $town = $this->createdTown();

        $this->assertSame('Newport', $town->town);
        $this->assertSame(1, $town->county_id);
        $this->assertSame('50.5,-4.6', $town->latlng);
        $this->assertSame('', $town->legacy);
        $this->assertNull($town->getFirstMedia('primary'));
    }

    #[Test]
    public function itStoresTheImage(): void
    {
        $this->createTown(['primary' => UploadedFile::fake()->image('town.jpg')])->assertHasNoFormErrors();

        $this->assertNotNull($this->createdTown()->getFirstMedia('primary'));
    }

    #[Test]
    public function itFillsTheSlugFromTheName(): void
    {
        Livewire::test(CreateEateryTown::class)
            ->set('data.town', 'Isle of Dogs')
            ->assertSet('data.slug', 'isle-of-dogs');
    }

    #[Test]
    public function itDoesNotOverwriteASlugThatHasBeenChanged(): void
    {
        Livewire::test(CreateEateryTown::class)
            ->set('data.town', 'Isle of Dogs')
            ->set('data.slug', 'custom')
            ->set('data.town', 'Newport')
            ->assertSet('data.slug', 'custom');
    }

    #[Test]
    public function itErrorsIfTheSlugIsUsedInTheSameCounty(): void
    {
        $this->create(EateryTown::class, ['slug' => 'newport', 'county_id' => 1]);

        $this->createTown()->assertHasFormErrors(['slug' => 'unique']);
    }

    #[Test]
    public function itAllowsASlugUsedInAnotherCounty(): void
    {
        $this->create(EateryTown::class, ['slug' => 'newport', 'county_id' => $this->london->id]);

        $this->createTown()->assertHasNoFormErrors();
    }

    #[Test]
    public function itErrorsWithoutTheRequiredFields(): void
    {
        $this->createTown(['town' => null, 'slug' => null])
            ->assertHasFormErrors(['town' => 'required', 'slug' => 'required']);
    }

    #[Test]
    public function itErrorsIfTheNameOrSlugAreTooLong(): void
    {
        $this->createTown(['town' => str_repeat('a', 201), 'slug' => str_repeat('a', 201)])
            ->assertHasFormErrors(['town' => 'max', 'slug' => 'max']);
    }

    #[Test]
    public function itOffersEveryCountyExceptLondonAndNationwide(): void
    {
        $this->create(EateryCounty::class, ['county' => 'Nationwide', 'slug' => 'nationwide']);

        Livewire::test(CreateEateryTown::class)->assertFormFieldExists(
            'county_id',
            fn (Select $field): bool => $field->getOptions() === [1 => 'England - Cheshire'],
        );
    }

    #[Test]
    public function itErrorsIfTheCountyIsLondon(): void
    {
        $this->createTown(['county_id' => $this->london->id])->assertHasFormErrors(['county_id']);
    }

    #[Test]
    public function itErrorsWithoutACounty(): void
    {
        $this->createTown(['county_id' => null])->assertHasFormErrors(['county_id' => 'required']);
    }

    #[Test]
    public function itPrefillsTheCountyFromTheUrl(): void
    {
        Livewire::withQueryParams(['county' => 1])->test(CreateEateryTown::class)->assertSet('data.county_id', 1);
    }

    /** @param array<string, mixed> $data */
    protected function createTown(array $data = []): Testable
    {
        return Livewire::test(CreateEateryTown::class)
            ->fillForm([
                'town' => 'Newport',
                'slug' => 'newport',
                'county_id' => 1,
                'latlng' => '50.5,-4.6',
                ...$data,
            ])
            ->call('create');
    }

    protected function createdTown(): EateryTown
    {
        return EateryTown::query()->withoutGlobalScopes()->where('slug', 'newport')->firstOrFail();
    }
}
