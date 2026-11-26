<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryReviews\Actions;

use App\Filament\Resources\EatingOut\EateryReviews\Pages\ListEateryReviews;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SetReviewBranchActionTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->eatery = $this->create(Eatery::class, ['county_id' => 1]);
    }

    #[Test]
    public function itIsVisibleForANationwideEatery(): void
    {
        $review = $this->build(EateryReview::class)->on($this->eatery)->create();

        Livewire::test(ListEateryReviews::class)->assertActionVisible(TestAction::make('setBranch')->table($review));
    }

    #[Test]
    public function itIsHiddenForAnEateryThatIsNotNationwide(): void
    {
        $this->create(EateryCounty::class, ['id' => 2]);
        $eatery = $this->create(Eatery::class, ['county_id' => 2]);
        $review = $this->build(EateryReview::class)->on($eatery)->create();

        Livewire::test(ListEateryReviews::class)->assertActionHidden(TestAction::make('setBranch')->table($review));
    }

    #[Test]
    public function itOnlyOffersTheEaterysOwnBranches(): void
    {
        $mine = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create();
        $theirs = $this->build(NationwideBranch::class)->forEatery($this->create(Eatery::class, ['county_id' => 1]))->create();
        $review = $this->build(EateryReview::class)->on($this->eatery)->create();

        Livewire::test(ListEateryReviews::class)
            ->mountAction(TestAction::make('setBranch')->table($review))
            ->assertFormFieldExists(
                'nationwide_branch_id',
                fn (Select $field): bool => array_keys($field->getOptions()) === [$mine->id] && ! array_key_exists($theirs->id, $field->getOptions()),
            );
    }

    #[Test]
    public function itSetsTheBranch(): void
    {
        $branch = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create();
        $review = $this->build(EateryReview::class)->on($this->eatery)->create();

        Livewire::test(ListEateryReviews::class)
            ->callAction(TestAction::make('setBranch')->table($review), ['nationwide_branch_id' => $branch->id])
            ->assertNotified();

        $this->assertSame($branch->id, $review->refresh()->nationwide_branch_id);
    }
}
