<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryRecommendations\Tables;

use App\Filament\Resources\EatingOut\EateryRecommendations\Pages\ListEateryRecommendations;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryRecommendationAiData;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryRecommendationsTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itShowsPendingRecommendationsFirstThenMostRecentlyUpdatedFirst(): void
    {
        $oldCompleted = $this->create(EateryRecommendation::class, ['completed' => true, 'updated_at' => now()->subDays(3)]);
        $newIgnored = $this->create(EateryRecommendation::class, ['ignored' => true, 'updated_at' => now()]);
        $oldPending = $this->create(EateryRecommendation::class, ['updated_at' => now()->subDays(2)]);
        $newPending = $this->create(EateryRecommendation::class, ['updated_at' => now()->subDay()]);

        Livewire::test(ListEateryRecommendations::class)
            ->assertCanSeeTableRecords([$newPending, $oldPending, $newIgnored, $oldCompleted], inOrder: true);
    }

    #[Test]
    public function itDoesNotShowMyPlaces(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);
        $myPlace = $this->create(EateryRecommendation::class, ['email' => 'alisondwheatley@gmail.com']);

        Livewire::test(ListEateryRecommendations::class)
            ->assertCanSeeTableRecords([$recommendation])
            ->assertCanNotSeeTableRecords([$myPlace]);
    }

    #[Test]
    public function itSearchesThePlaceNameLocationAndDetails(): void
    {
        $byName = $this->create(EateryRecommendation::class, ['place_name' => 'Gluten Free Haven']);
        $byLocation = $this->create(EateryRecommendation::class, ['place_location' => 'Haven Street, Crewe']);
        $byDetails = $this->create(EateryRecommendation::class, ['place_details' => 'A real haven for coeliacs']);
        $other = $this->create(EateryRecommendation::class, ['place_name' => 'Other', 'place_location' => 'Elsewhere', 'place_details' => 'Nothing']);

        Livewire::test(ListEateryRecommendations::class)
            ->searchTable('haven')
            ->assertCanSeeTableRecords([$byName, $byLocation, $byDetails])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itFiltersByCompleted(): void
    {
        $completed = $this->create(EateryRecommendation::class, ['completed' => true]);
        $pending = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)
            ->filterTable('completed', false)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$completed]);
    }

    #[Test]
    public function itFiltersByIgnored(): void
    {
        $ignored = $this->create(EateryRecommendation::class, ['ignored' => true]);
        $pending = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)
            ->filterTable('ignored', false)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$ignored]);
    }

    #[Test]
    public function itShowsNoAiStatusWithoutAiData(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)->assertTableColumnStateSet('ai_status', null, $recommendation);
    }

    #[Test]
    public function itShowsTheAiCheckIsInProgress(): void
    {
        $recommendation = $this->recommendationWithAiData(['completed_at' => null]);

        Livewire::test(ListEateryRecommendations::class)->assertTableColumnStateSet('ai_status', 'AI: Checking…', $recommendation);
    }

    #[Test]
    public function itShowsTheAiCheckFailed(): void
    {
        $recommendation = $this->recommendationWithAiData(['failed_at' => now()]);

        Livewire::test(ListEateryRecommendations::class)->assertTableColumnStateSet('ai_status', 'AI: Failed', $recommendation);
    }

    #[Test]
    public function itShowsTheRecommendationIsEligible(): void
    {
        $recommendation = $this->recommendationWithAiData(['completed_at' => now()]);

        Livewire::test(ListEateryRecommendations::class)->assertTableColumnStateSet('ai_status', 'AI: Eligible', $recommendation);
    }

    #[Test]
    public function itShowsTheRecommendationIsNotEligible(): void
    {
        $recommendation = $this->recommendationWithAiData(['completed_at' => now(), 'is_eligible' => false]);

        Livewire::test(ListEateryRecommendations::class)->assertTableColumnStateSet('ai_status', 'AI: Not eligible', $recommendation);
    }

    #[Test]
    public function itPollsWhileAnAiCheckIsInProgress(): void
    {
        $this->recommendationWithAiData(['completed_at' => null]);

        $this->assertSame('5s', Livewire::test(ListEateryRecommendations::class)->instance()->getTable()->getPollingInterval());
    }

    #[Test]
    public function itDoesNotPollWhenNoAiCheckIsInProgress(): void
    {
        $this->recommendationWithAiData(['completed_at' => now()]);

        $this->assertNull(Livewire::test(ListEateryRecommendations::class)->instance()->getTable()->getPollingInterval());
    }

    #[Test]
    public function itDeletesARecommendation(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)->callAction(TestAction::make(DeleteAction::class)->table($recommendation));

        $this->assertDatabaseEmpty(EateryRecommendation::class);
    }

    /** @param array<string, mixed> $attributes */
    protected function recommendationWithAiData(array $attributes = []): EateryRecommendation
    {
        $recommendation = $this->create(EateryRecommendation::class);

        $this->build(EateryRecommendationAiData::class)->on($recommendation)->create($attributes);

        return $recommendation;
    }
}
