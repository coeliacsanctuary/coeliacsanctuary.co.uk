<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryBoroughs\Pages;

use App\Filament\Resources\EatingOut\EateryBoroughs\Pages\CreateEateryBorough;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateEateryBoroughTest extends TestCase
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
    public function itCreatesTheBoroughWithoutAnImage(): void
    {
        $this->createBorough()->assertHasNoFormErrors()->assertNotified()->assertRedirect();

        $borough = $this->createdBorough();

        $this->assertSame('Newport', $borough->town);
        $this->assertSame($this->london->id, $borough->county_id);
        $this->assertSame('50.5,-4.6', $borough->latlng);
        $this->assertSame('', $borough->legacy);
        $this->assertNull($borough->getFirstMedia('primary'));
    }

    #[Test]
    public function itStoresTheImage(): void
    {
        $this->createBorough(['primary' => UploadedFile::fake()->image('town.jpg')])->assertHasNoFormErrors();

        $this->assertNotNull($this->createdBorough()->getFirstMedia('primary'));
    }

    #[Test]
    public function itFillsTheSlugFromTheName(): void
    {
        Livewire::test(CreateEateryBorough::class)
            ->set('data.town', 'Isle of Dogs')
            ->assertSet('data.slug', 'isle-of-dogs');
    }

    #[Test]
    public function itDoesNotOverwriteASlugThatHasBeenChanged(): void
    {
        Livewire::test(CreateEateryBorough::class)
            ->set('data.town', 'Isle of Dogs')
            ->set('data.slug', 'custom')
            ->set('data.town', 'Newport')
            ->assertSet('data.slug', 'custom');
    }

    #[Test]
    public function itErrorsIfTheSlugIsUsedInTheSameCounty(): void
    {
        $this->create(EateryTown::class, ['slug' => 'newport', 'county_id' => $this->london->id]);

        $this->createBorough()->assertHasFormErrors(['slug' => 'unique']);
    }

    #[Test]
    public function itAllowsASlugUsedInAnotherCounty(): void
    {
        $this->create(EateryTown::class, ['slug' => 'newport', 'county_id' => 1]);

        $this->createBorough()->assertHasNoFormErrors();
    }

    #[Test]
    public function itErrorsWithoutTheRequiredFields(): void
    {
        $this->createBorough(['town' => null, 'slug' => null])
            ->assertHasFormErrors(['town' => 'required', 'slug' => 'required']);
    }

    #[Test]
    public function itErrorsIfTheNameOrSlugAreTooLong(): void
    {
        $this->createBorough(['town' => str_repeat('a', 201), 'slug' => str_repeat('a', 201)])
            ->assertHasFormErrors(['town' => 'max', 'slug' => 'max']);
    }

    #[Test]
    public function itHasNoCountyField(): void
    {
        Livewire::test(CreateEateryBorough::class)->assertFormFieldDoesNotExist('county_id');
    }

    /** @param array<string, mixed> $data */
    protected function createBorough(array $data = []): Testable
    {
        return Livewire::test(CreateEateryBorough::class)
            ->fillForm([
                'town' => 'Newport',
                'slug' => 'newport',
                'latlng' => '50.5,-4.6',
                ...$data,
            ])
            ->call('create');
    }

    protected function createdBorough(): EateryTown
    {
        return EateryTown::query()->withoutGlobalScopes()->where('slug', 'newport')->firstOrFail();
    }
}
