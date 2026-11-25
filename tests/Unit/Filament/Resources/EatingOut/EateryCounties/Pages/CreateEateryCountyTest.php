<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryCounties\Pages;

use App\Filament\Resources\EatingOut\EateryCounties\Pages\CreateEateryCounty;
use App\Models\EatingOut\EateryCounty;
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

class CreateEateryCountyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        Storage::fake('media');
    }

    #[Test]
    public function itCreatesTheCountyWithoutAnImage(): void
    {
        $this->createCounty()->assertHasNoFormErrors()->assertNotified()->assertRedirect();

        $county = $this->createdCounty();

        $this->assertSame('Cornwall', $county->county);
        $this->assertSame('cornwall', $county->slug);
        $this->assertSame(1, $county->country_id);
        $this->assertSame('50.5,-4.6', $county->latlng);
        $this->assertSame('', $county->legacy);
        $this->assertNull($county->getFirstMedia('primary'));
    }

    #[Test]
    public function itStoresTheImage(): void
    {
        $this->createCounty(['primary' => UploadedFile::fake()->image('county.jpg')])->assertHasNoFormErrors();

        $this->assertNotNull($this->createdCounty()->getFirstMedia('primary'));
    }

    #[Test]
    public function itFillsTheSlugFromTheName(): void
    {
        Livewire::test(CreateEateryCounty::class)
            ->set('data.county', 'Isle of Wight')
            ->assertSet('data.slug', 'isle-of-wight');
    }

    #[Test]
    public function itDoesNotOverwriteASlugThatHasBeenChanged(): void
    {
        Livewire::test(CreateEateryCounty::class)
            ->set('data.county', 'Isle of Wight')
            ->set('data.slug', 'custom')
            ->set('data.county', 'Cornwall')
            ->assertSet('data.slug', 'custom');
    }

    #[Test]
    public function itErrorsIfTheSlugIsUsedInAnotherCountry(): void
    {
        $this->create(EateryCounty::class, ['slug' => 'cornwall', 'country_id' => 2]);

        $this->createCounty()->assertHasFormErrors(['slug' => 'unique']);
    }

    #[Test]
    public function itErrorsWithoutTheRequiredFields(): void
    {
        $this->createCounty(['county' => null, 'slug' => null, 'country_id' => null])
            ->assertHasFormErrors(['county' => 'required', 'slug' => 'required', 'country_id' => 'required']);
    }

    #[Test]
    public function itErrorsIfTheNameOrSlugAreTooLong(): void
    {
        $this->createCounty(['county' => str_repeat('a', 201), 'slug' => str_repeat('a', 201)])
            ->assertHasFormErrors(['county' => 'max', 'slug' => 'max']);
    }

    /** @param array<string, mixed> $data */
    protected function createCounty(array $data = []): Testable
    {
        return Livewire::test(CreateEateryCounty::class)
            ->fillForm([
                'county' => 'Cornwall',
                'slug' => 'cornwall',
                'country_id' => 1,
                'latlng' => '50.5,-4.6',
                ...$data,
            ])
            ->call('create');
    }

    protected function createdCounty(): EateryCounty
    {
        return EateryCounty::query()->withoutGlobalScopes()->where('slug', 'cornwall')->firstOrFail();
    }
}
