<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryReports\Tables;

use App\Filament\Resources\EatingOut\EateryReports\Pages\ListEateryReports;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReport;
use App\Models\EatingOut\NationwideBranch;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryReportsTableTest extends TestCase
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
    public function itShowsPendingReportsFirstThenNewestFirst(): void
    {
        $oldCompleted = $this->report(['completed' => true, 'created_at' => now()->subDays(3)]);
        $newIgnored = $this->report(['ignored' => true, 'created_at' => now()]);
        $oldPending = $this->report(['created_at' => now()->subDays(2)]);
        $newPending = $this->report(['created_at' => now()->subDay()]);

        Livewire::test(ListEateryReports::class)
            ->assertCanSeeTableRecords([$newPending, $oldPending, $newIgnored, $oldCompleted], inOrder: true);
    }

    #[Test]
    public function itShowsTheEateryFullName(): void
    {
        $report = $this->report();

        Livewire::test(ListEateryReports::class)
            ->assertTableColumnStateSet('eatery_name', $this->eatery->load(['area', 'town', 'county', 'country'])->full_name, $report);
    }

    #[Test]
    public function itShowsTheBranchFullName(): void
    {
        $branch = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create();

        $report = $this->report(['branch_id' => $branch->id]);

        Livewire::test(ListEateryReports::class)
            ->assertTableColumnStateSet('branch_full_name', $branch->load(['eatery', 'area', 'town', 'county', 'country'])->full_name, $report);
    }

    #[Test]
    public function itFiltersByCompleted(): void
    {
        $completed = $this->report(['completed' => true]);
        $pending = $this->report();

        Livewire::test(ListEateryReports::class)
            ->filterTable('completed', false)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$completed]);
    }

    #[Test]
    public function itFiltersByIgnored(): void
    {
        $ignored = $this->report(['ignored' => true]);
        $pending = $this->report();

        Livewire::test(ListEateryReports::class)
            ->filterTable('ignored', false)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$ignored]);
    }

    #[Test]
    public function itDeletesAReport(): void
    {
        $report = $this->report();

        Livewire::test(ListEateryReports::class)->callAction(TestAction::make(DeleteAction::class)->table($report));

        $this->assertDatabaseEmpty(EateryReport::class);
    }

    /** @param array<string, mixed> $attributes */
    protected function report(array $attributes = []): EateryReport
    {
        return $this->create(EateryReport::class, ['wheretoeat_id' => $this->eatery->id, ...$attributes]);
    }
}
