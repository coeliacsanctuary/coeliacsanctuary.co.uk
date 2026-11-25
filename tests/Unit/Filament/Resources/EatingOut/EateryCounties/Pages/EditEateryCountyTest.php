<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryCounties\Pages;

use App\Filament\Resources\EatingOut\EateryCounties\Pages\EditEateryCounty;
use App\Models\EatingOut\EateryCounty;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditEateryCountyTest extends TestCase
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
    public function itFillsTheFormFromTheCounty(): void
    {
        $this->editPage()->assertSchemaStateSet([
            'county' => 'Cornwall',
            'country_id' => 1,
            'latlng' => '50.5,-4.6',
        ]);
    }

    #[Test]
    public function itSavesWithoutTouchingTheSlugOrLegacy(): void
    {
        $this->editPage()
            ->set('data.county', 'New Cornwall')
            ->call('save')
            ->assertHasNoFormErrors();

        $county = EateryCounty::query()->withoutGlobalScopes()->findOrFail($this->county->id);

        $this->assertSame('New Cornwall', $county->county);
        $this->assertSame('cornwall', $county->slug);
        $this->assertSame('Old Cornwall', $county->legacy);
    }

    #[Test]
    public function itDoesNotShowTheSlug(): void
    {
        $this->editPage()->assertFormFieldHidden('slug');
    }

    #[Test]
    public function itCannotBeDeleted(): void
    {
        $this->editPage()->assertActionDoesNotExist('delete');
    }

    protected function editPage(): Testable
    {
        return Livewire::test(EditEateryCounty::class, ['record' => $this->county->slug]);
    }
}
