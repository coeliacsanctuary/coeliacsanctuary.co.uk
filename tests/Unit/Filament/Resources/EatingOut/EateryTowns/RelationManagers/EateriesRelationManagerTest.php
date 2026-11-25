<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryTowns\RelationManagers;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\EatingOut\EateryBoroughs\Pages\EditEateryBorough;
use App\Filament\Resources\EatingOut\EateryTowns\Pages\EditEateryTown;
use App\Filament\Resources\EatingOut\EateryTowns\RelationManagers\EateriesRelationManager;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
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
    protected EateryCounty $london;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london']);
    }

    #[Test]
    public function itIsShownOnTownsAndBoroughs(): void
    {
        $borough = $this->create(EateryTown::class, ['county_id' => $this->london->id]);

        Livewire::test(EditEateryTown::class, ['record' => 1])->assertSeeLivewire(EateriesRelationManager::class);
        Livewire::test(EditEateryBorough::class, ['record' => $borough->id])->assertSeeLivewire(EateriesRelationManager::class);
    }

    #[Test]
    public function itOnlyListsEateriesInTheTownIncludingThoseNotLive(): void
    {
        $live = $this->create(Eatery::class);
        $notLive = $this->build(Eatery::class)->notLive()->create();
        $elsewhere = $this->create(Eatery::class, ['town_id' => $this->create(EateryTown::class, ['county_id' => 1])->id]);

        $this->eateriesPanel()
            ->assertCanSeeTableRecords([$live, $notLive])
            ->assertCanNotSeeTableRecords([$elsewhere]);
    }

    #[Test]
    public function itShowsTheReviewCount(): void
    {
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, 2, ['wheretoeat_id' => $eatery->id]);

        $this->eateriesPanel()->assertTableColumnStateSet('reviews_count', 2, $eatery);
    }

    #[Test]
    public function itOpensTheCreatePageForTheTown(): void
    {
        $this->eateriesPanel()->assertActionHasUrl(
            TestAction::make('create')->table(),
            EateryResource::getUrl('create', ['town' => 1]),
        );
    }

    protected function eateriesPanel(): Testable
    {
        return Livewire::test(EateriesRelationManager::class, [
            'ownerRecord' => EateryTown::query()->withoutGlobalScopes()->findOrFail(1),
            'pageClass' => EditEateryTown::class,
        ]);
    }
}
