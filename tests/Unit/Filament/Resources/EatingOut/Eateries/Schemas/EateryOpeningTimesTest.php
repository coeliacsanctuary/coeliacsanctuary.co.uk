<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Schemas;

use App\Enums\EatingOut\EateryType;
use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryOpeningTimes as EateryOpeningTimesModel;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryOpeningTimesTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itIsVisibleForAnEatery(): void
    {
        $this->withOpeningTimes();

        $this->editEatery()->assertSchemaComponentVisible('openingTimes.monday_start_h');
    }

    #[Test]
    public function itIsHiddenForAnAttraction(): void
    {
        $this->withOpeningTimes();

        $this->editEatery()
            ->set('data.type_id', EateryType::ATTRACTION->value)
            ->assertSchemaComponentHidden('openingTimes.monday_start_h');
    }

    #[Test]
    public function itIsHiddenForAHotel(): void
    {
        $this->withOpeningTimes();

        $this->editEatery()
            ->set('data.type_id', EateryType::HOTEL->value)
            ->assertSchemaComponentHidden('openingTimes.monday_start_h');
    }

    #[Test]
    public function itDefaultsToUnknownWhenTheEateryHasNoOpeningTimes(): void
    {
        $this->editEatery()->assertSchemaComponentStateSet('no_opening_times', true);
    }

    #[Test]
    public function itReadsTheExistingOpeningTimes(): void
    {
        $this->create(EateryOpeningTimesModel::class, [
            'wheretoeat_id' => $this->eatery->id,
            'monday_start' => '09:30:00',
            'monday_end' => '17:45:00',
        ]);

        $this->editEatery()
            ->assertSchemaComponentStateSet('no_opening_times', false)
            ->assertSchemaComponentStateSet('openingTimes.monday_start_h', 9)
            ->assertSchemaComponentStateSet('openingTimes.monday_start_m', 30)
            ->assertSchemaComponentStateSet('openingTimes.monday_end_h', 17)
            ->assertSchemaComponentStateSet('openingTimes.monday_end_m', 45)
            ->assertSchemaComponentStateSet('openingTimes.monday_closed', false);
    }

    #[Test]
    public function itMarksADayAsClosedWhenTheEateryHasNoTimesForIt(): void
    {
        $this->create(EateryOpeningTimesModel::class, [
            'wheretoeat_id' => $this->eatery->id,
            'monday_start' => null,
            'monday_end' => null,
        ]);

        $this->editEatery()->assertSchemaComponentStateSet('openingTimes.monday_closed', true);
    }

    #[Test]
    public function itStoresTheOpeningTimes(): void
    {
        $this->withOpeningTimes();

        $this->editEatery()
            ->fillForm([
                'openingTimes.monday_closed' => false,
                'openingTimes.monday_start_h' => 8,
                'openingTimes.monday_start_m' => 15,
                'openingTimes.monday_end_h' => 20,
                'openingTimes.monday_end_m' => 30,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $openingTimes = $this->eatery->refresh()->openingTimes;

        $this->assertSame('08:15:00', $openingTimes->monday_start);
        $this->assertSame('20:30:00', $openingTimes->monday_end);
    }

    #[Test]
    public function itStoresADayAsClosed(): void
    {
        $this->withOpeningTimes();

        $this->editEatery()
            ->fillForm(['openingTimes.monday_closed' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $openingTimes = $this->eatery->refresh()->openingTimes;

        $this->assertNull($openingTimes->monday_start);
        $this->assertNull($openingTimes->monday_end);
    }

    protected function withOpeningTimes(array $overrides = []): void
    {
        $this->create(EateryOpeningTimesModel::class, ['wheretoeat_id' => $this->eatery->id, ...$overrides]);
    }

    protected function editEatery(): Testable
    {
        return Livewire::test(EditEatery::class, ['record' => $this->eatery->id]);
    }
}
