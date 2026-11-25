<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryCuisines\RelationManagers;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\EatingOut\EateryCuisines\Pages\EditEateryCuisine;
use App\Filament\Resources\EatingOut\EateryCuisines\RelationManagers\EateriesRelationManager;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryReview;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateriesRelationManagerTest extends TestCase
{
    protected EateryCuisine $cuisine;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->cuisine = $this->create(EateryCuisine::class, ['cuisine' => 'Peruvian']);
    }

    #[Test]
    public function itIsShownOnTheEditPage(): void
    {
        Livewire::test(EditEateryCuisine::class, ['record' => $this->cuisine->id])->assertSeeLivewire(EateriesRelationManager::class);
    }

    #[Test]
    public function itOnlyListsEateriesWithTheCuisineIncludingThoseNotLive(): void
    {
        $live = $this->create(Eatery::class, ['cuisine_id' => $this->cuisine->id]);
        $notLive = $this->build(Eatery::class)->notLive()->create(['cuisine_id' => $this->cuisine->id]);
        $otherCuisine = $this->create(Eatery::class, ['cuisine_id' => 1]);

        $this->eateriesPanel($this->cuisine)
            ->assertCanSeeTableRecords([$live, $notLive])
            ->assertCanNotSeeTableRecords([$otherCuisine]);
    }

    #[Test]
    public function itShowsTheReviewCount(): void
    {
        $eatery = $this->create(Eatery::class, ['cuisine_id' => $this->cuisine->id]);

        $this->create(EateryReview::class, 2, ['wheretoeat_id' => $eatery->id]);

        $this->eateriesPanel($this->cuisine)->assertTableColumnStateSet('reviews_count', 2, $eatery);
    }

    #[Test]
    public function itOpensTheCreatePageForTheCuisine(): void
    {
        $this->eateriesPanel($this->cuisine)->assertActionHasUrl(
            TestAction::make('create')->table(),
            EateryResource::getUrl('create', ['cuisine' => $this->cuisine->id]),
        );
    }

    #[Test]
    public function itCannotCreateAnEateryForTheBlankCuisine(): void
    {
        $blank = $this->create(EateryCuisine::class, ['id' => 29, 'cuisine' => '']);

        $this->eateriesPanel($blank)->assertActionHidden(TestAction::make('create')->table());
    }

    protected function eateriesPanel(EateryCuisine $cuisine): Testable
    {
        return Livewire::test(EateriesRelationManager::class, [
            'ownerRecord' => $cuisine,
            'pageClass' => EditEateryCuisine::class,
        ]);
    }
}
