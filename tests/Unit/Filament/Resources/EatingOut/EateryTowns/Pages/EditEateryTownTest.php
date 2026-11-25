<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryTowns\Pages;

use App\Filament\Resources\EatingOut\EateryTowns\Pages\EditEateryTown;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditEateryTownTest extends TestCase
{
    protected EateryCounty $london;

    protected EateryTown $town;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london']);

        $this->town = $this->create(EateryTown::class, ['town' => 'Newport', 'slug' => 'newport', 'legacy' => 'old-newport', 'latlng' => '50.5,-4.6', 'county_id' => 1]);
    }

    #[Test]
    public function itFillsTheForm(): void
    {
        $this->editPage()->assertSchemaStateSet(['town' => 'Newport', 'latlng' => '50.5,-4.6', 'county_id' => 1]);
    }

    #[Test]
    public function itSavesWithoutTouchingTheSlugCountyOrLegacy(): void
    {
        $this->editPage()
            ->set('data.town', 'New Newport')
            ->call('save')
            ->assertHasNoFormErrors();

        $town = EateryTown::query()->withoutGlobalScopes()->findOrFail($this->town->id);

        $this->assertSame('New Newport', $town->town);
        $this->assertSame('newport', $town->slug);
        $this->assertSame('old-newport', $town->legacy);
        $this->assertSame(1, $town->county_id);
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
        return Livewire::test(EditEateryTown::class, ['record' => $this->town->id]);
    }
}
