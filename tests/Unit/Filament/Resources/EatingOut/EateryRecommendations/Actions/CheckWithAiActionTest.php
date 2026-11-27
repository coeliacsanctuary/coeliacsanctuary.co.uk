<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryRecommendations\Actions;

use App\Actions\EatingOut\CheckEateryRecommendationWithAiAction;
use App\Filament\Resources\EatingOut\EateryRecommendations\Pages\ListEateryRecommendations;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryRecommendationAiData;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Action;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckWithAiActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itIsVisibleWhenTheRecommendationHasNoAiData(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)->assertActionVisible(TestAction::make('checkWithAi')->table($recommendation));
    }

    #[Test]
    public function itIsHiddenWhenTheRecommendationHasAiData(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        $this->build(EateryRecommendationAiData::class)->on($recommendation)->create();

        Livewire::test(ListEateryRecommendations::class)->assertActionHidden(TestAction::make('checkWithAi')->table($recommendation));
    }

    #[Test]
    public function itConfirmsBeforeRunning(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)->assertActionExists(
            TestAction::make('checkWithAi')->table($recommendation),
            fn (Action $action): bool => $action->isConfirmationRequired(),
        );
    }

    #[Test]
    public function itQueuesTheRecommendationToBeCheckedWithAi(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        $this->expectAction(CheckEateryRecommendationWithAiAction::class);

        Livewire::test(ListEateryRecommendations::class)
            ->callAction(TestAction::make('checkWithAi')->table($recommendation))
            ->assertNotified();
    }
}
