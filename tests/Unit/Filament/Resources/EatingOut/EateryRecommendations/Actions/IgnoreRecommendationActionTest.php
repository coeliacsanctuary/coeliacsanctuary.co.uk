<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryRecommendations\Actions;

use App\Filament\Resources\EatingOut\EateryRecommendations\Pages\ListEateryRecommendations;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Action;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IgnoreRecommendationActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itIsVisibleForAPendingRecommendation(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)->assertActionVisible(TestAction::make('ignore')->table($recommendation));
    }

    #[Test]
    public function itIsHiddenForACompletedRecommendation(): void
    {
        $recommendation = $this->create(EateryRecommendation::class, ['completed' => true]);

        Livewire::test(ListEateryRecommendations::class)->assertActionHidden(TestAction::make('ignore')->table($recommendation));
    }

    #[Test]
    public function itIsHiddenForAnIgnoredRecommendation(): void
    {
        $recommendation = $this->create(EateryRecommendation::class, ['ignored' => true]);

        Livewire::test(ListEateryRecommendations::class)->assertActionHidden(TestAction::make('ignore')->table($recommendation));
    }

    #[Test]
    public function itConfirmsBeforeRunning(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)->assertActionExists(
            TestAction::make('ignore')->table($recommendation),
            fn (Action $action): bool => $action->isConfirmationRequired(),
        );
    }

    #[Test]
    public function itIgnoresTheRecommendation(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)
            ->callAction(TestAction::make('ignore')->table($recommendation))
            ->assertNotified();

        $this->assertTrue($recommendation->refresh()->ignored);
    }
}
