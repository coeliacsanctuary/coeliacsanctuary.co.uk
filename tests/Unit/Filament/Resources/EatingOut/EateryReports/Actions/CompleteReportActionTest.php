<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryReports\Actions;

use App\Filament\Resources\EatingOut\EateryReports\Pages\ListEateryReports;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReport;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Action;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompleteReportActionTest extends TestCase
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
    public function itIsVisibleForAPendingReport(): void
    {
        $report = $this->report();

        Livewire::test(ListEateryReports::class)->assertActionVisible(TestAction::make('complete')->table($report));
    }

    #[Test]
    public function itIsHiddenForACompletedReport(): void
    {
        $report = $this->report(['completed' => true]);

        Livewire::test(ListEateryReports::class)->assertActionHidden(TestAction::make('complete')->table($report));
    }

    #[Test]
    public function itIsHiddenForAnIgnoredReport(): void
    {
        $report = $this->report(['ignored' => true]);

        Livewire::test(ListEateryReports::class)->assertActionHidden(TestAction::make('complete')->table($report));
    }

    #[Test]
    public function itConfirmsBeforeRunning(): void
    {
        $report = $this->report();

        Livewire::test(ListEateryReports::class)->assertActionExists(
            TestAction::make('complete')->table($report),
            fn (Action $action): bool => $action->isConfirmationRequired(),
        );
    }

    #[Test]
    public function itCompletesTheReport(): void
    {
        $report = $this->report();

        Livewire::test(ListEateryReports::class)
            ->callAction(TestAction::make('complete')->table($report))
            ->assertNotified();

        $this->assertTrue($report->refresh()->completed);
    }

    /** @param array<string, mixed> $attributes */
    protected function report(array $attributes = []): EateryReport
    {
        return $this->create(EateryReport::class, ['wheretoeat_id' => $this->eatery->id, ...$attributes]);
    }
}
