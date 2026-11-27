<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryRecommendations\Actions;

use App\Filament\Resources\EatingOut\EateryRecommendations\Pages\ListEateryRecommendations;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryRecommendationAiData;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewAiDataActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itIsHiddenWhenTheRecommendationHasNoAiData(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)->assertActionHidden(TestAction::make('viewAiData')->table($recommendation));
    }

    #[Test]
    public function itShowsTheAiData(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        $this->build(EateryRecommendationAiData::class)->on($recommendation)->create([
            'place_name' => 'The AI Cafe',
            'explanation' => 'It looks like a great place.',
            'features' => ['Takeaway'],
        ]);

        Livewire::test(ListEateryRecommendations::class)
            ->assertActionVisible(TestAction::make('viewAiData')->table($recommendation))
            ->mountAction(TestAction::make('viewAiData')->table($recommendation))
            ->assertSchemaComponentExists('aiData.place_name', checkComponentUsing: fn (TextEntry $entry): bool => $entry->getState() === 'The AI Cafe')
            ->assertSchemaComponentExists('aiData.explanation', checkComponentUsing: fn (TextEntry $entry): bool => $entry->getState() === 'It looks like a great place.')
            ->assertSchemaComponentExists('aiData.features', checkComponentUsing: fn (TextEntry $entry): bool => $entry->getState() === ['Takeaway']);
    }
}
