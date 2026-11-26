<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryAlerts\Tables;

use App\Filament\Resources\EatingOut\EateryAlerts\Pages\ListEateryAlerts;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAlert;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryAlertsTableTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->eatery = $this->create(Eatery::class, ['name' => 'Test Eatery', 'town_id' => 1, 'county_id' => 1, 'country_id' => 1]);
    }

    #[Test]
    public function itShowsPendingAlertsFirstThenNewestFirst(): void
    {
        $oldCompleted = $this->build(EateryAlert::class)->on($this->eatery)->completed()->create(['created_at' => now()->subDays(3)]);
        $newIgnored = $this->build(EateryAlert::class)->on($this->eatery)->ignored()->create(['created_at' => now()]);
        $oldPending = $this->build(EateryAlert::class)->on($this->eatery)->create(['created_at' => now()->subDays(2)]);
        $newPending = $this->build(EateryAlert::class)->on($this->eatery)->create(['created_at' => now()->subDay()]);

        Livewire::test(ListEateryAlerts::class)
            ->assertCanSeeTableRecords([$newPending, $oldPending, $newIgnored, $oldCompleted], inOrder: true);
    }

    #[Test]
    public function itShowsTheEateryFullName(): void
    {
        $alert = $this->build(EateryAlert::class)->on($this->eatery)->create();

        Livewire::test(ListEateryAlerts::class)
            ->assertTableColumnStateSet('eatery_name', $this->eatery->load(['area', 'town', 'county', 'country'])->full_name, $alert);
    }

    #[Test]
    public function itFiltersByCompleted(): void
    {
        $completed = $this->build(EateryAlert::class)->on($this->eatery)->completed()->create();
        $pending = $this->build(EateryAlert::class)->on($this->eatery)->create();

        Livewire::test(ListEateryAlerts::class)
            ->filterTable('completed', false)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$completed]);
    }

    #[Test]
    public function itFiltersByIgnored(): void
    {
        $ignored = $this->build(EateryAlert::class)->on($this->eatery)->ignored()->create();
        $pending = $this->build(EateryAlert::class)->on($this->eatery)->create();

        Livewire::test(ListEateryAlerts::class)
            ->filterTable('ignored', false)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$ignored]);
    }

    #[Test]
    public function itDeletesAnAlert(): void
    {
        $alert = $this->build(EateryAlert::class)->on($this->eatery)->create();

        Livewire::test(ListEateryAlerts::class)->callAction(TestAction::make(DeleteAction::class)->table($alert));

        $this->assertDatabaseEmpty(EateryAlert::class);
    }
}
