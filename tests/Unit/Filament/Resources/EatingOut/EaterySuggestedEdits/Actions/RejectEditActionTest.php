<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EaterySuggestedEdits\Actions;

use App\Filament\Resources\EatingOut\EaterySuggestedEdits\Pages\ListEaterySuggestedEdits;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EaterySuggestedEdit;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Action;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RejectEditActionTest extends TestCase
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
    public function itIsVisibleForAPendingEdit(): void
    {
        $edit = $this->edit();

        Livewire::test(ListEaterySuggestedEdits::class)->assertActionVisible(TestAction::make('reject')->table($edit));
    }

    #[Test]
    public function itIsHiddenForAnAcceptedEdit(): void
    {
        $edit = $this->edit(['accepted' => true]);

        Livewire::test(ListEaterySuggestedEdits::class)->assertActionHidden(TestAction::make('reject')->table($edit));
    }

    #[Test]
    public function itIsHiddenForARejectedEdit(): void
    {
        $edit = $this->edit(['rejected' => true]);

        Livewire::test(ListEaterySuggestedEdits::class)->assertActionHidden(TestAction::make('reject')->table($edit));
    }

    #[Test]
    public function itDoesNotConfirmBeforeRunning(): void
    {
        $edit = $this->edit();

        Livewire::test(ListEaterySuggestedEdits::class)->assertActionExists(
            TestAction::make('reject')->table($edit),
            fn (Action $action): bool => ! $action->isConfirmationRequired(),
        );
    }

    #[Test]
    public function itRejectsTheEdit(): void
    {
        $edit = $this->edit();

        Livewire::test(ListEaterySuggestedEdits::class)
            ->callAction(TestAction::make('reject')->table($edit))
            ->assertNotified();

        $this->assertTrue($edit->refresh()->rejected);
    }

    /** @param array<string, mixed> $attributes */
    protected function edit(array $attributes = []): EaterySuggestedEdit
    {
        return $this->create(EaterySuggestedEdit::class, ['wheretoeat_id' => $this->eatery->id, ...$attributes]);
    }
}
