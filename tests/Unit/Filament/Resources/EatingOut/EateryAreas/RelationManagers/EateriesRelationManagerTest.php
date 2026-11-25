<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryAreas\RelationManagers;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\EatingOut\EateryAreas\Pages\EditEateryArea;
use App\Filament\Resources\EatingOut\EateryAreas\RelationManagers\EateriesRelationManager;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
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

    protected EateryTown $borough;

    protected EateryArea $area;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london', 'country_id' => 1]);
        $this->borough = $this->create(EateryTown::class, ['town' => 'Camden', 'slug' => 'camden', 'county_id' => $this->london->id]);
        $this->area = $this->create(EateryArea::class, ['area' => 'Holborn', 'slug' => 'holborn', 'latlng' => '51.5,-0.1', 'town_id' => $this->borough->id]);
    }

    #[Test]
    public function itIsShownOnTheEditPage(): void
    {
        Livewire::test(EditEateryArea::class, ['record' => $this->area->id])->assertSeeLivewire(EateriesRelationManager::class);
    }

    #[Test]
    public function itOnlyListsEateriesInTheAreaIncludingThoseNotLive(): void
    {
        $live = $this->create(Eatery::class, ['area_id' => $this->area->id]);
        $notLive = $this->build(Eatery::class)->notLive()->create(['area_id' => $this->area->id]);
        $elsewhere = $this->create(Eatery::class, ['area_id' => $this->create(EateryArea::class, ['town_id' => $this->borough->id])->id]);

        $this->eateriesPanel()
            ->assertCanSeeTableRecords([$live, $notLive])
            ->assertCanNotSeeTableRecords([$elsewhere]);
    }

    #[Test]
    public function itShowsTheReviewCount(): void
    {
        $eatery = $this->create(Eatery::class, ['area_id' => $this->area->id]);

        $this->create(EateryReview::class, 2, ['wheretoeat_id' => $eatery->id]);

        $this->eateriesPanel()->assertTableColumnStateSet('reviews_count', 2, $eatery);
    }

    #[Test]
    public function itOpensTheCreatePageForTheArea(): void
    {
        $this->eateriesPanel()->assertActionHasUrl(
            TestAction::make('create')->table(),
            EateryResource::getUrl('create', ['area' => $this->area->id]),
        );
    }

    protected function eateriesPanel(): Testable
    {
        return Livewire::test(EateriesRelationManager::class, [
            'ownerRecord' => $this->area,
            'pageClass' => EditEateryArea::class,
        ]);
    }
}
