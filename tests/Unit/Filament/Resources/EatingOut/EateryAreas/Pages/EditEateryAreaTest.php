<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryAreas\Pages;

use App\Filament\Resources\EatingOut\EateryAreas\Pages\EditEateryArea;
use App\Models\EatingOut\EateryArea;
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

class EditEateryAreaTest extends TestCase
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
    }

    #[Test]
    public function itFillsTheForm(): void
    {
        $this->editPage()->assertSchemaStateSet(['area' => 'Holborn', 'latlng' => '51.5,-0.1', 'town_id' => $this->borough->id]);
    }

    #[Test]
    public function itSavesWithoutTouchingTheSlugOrBorough(): void
    {
        $this->editPage()
            ->set('data.area', 'New Holborn')
            ->call('save')
            ->assertHasNoFormErrors();

        $area = EateryArea::query()->withoutGlobalScopes()->findOrFail($this->area->id);

        $this->assertSame('New Holborn', $area->area);
        $this->assertSame('holborn', $area->slug);
        $this->assertSame($this->borough->id, $area->town_id);
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
        return Livewire::test(EditEateryArea::class, ['record' => $this->area->id]);
    }
}
