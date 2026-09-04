<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\RelationManagers;

use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\ReviewsRelationManager;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReviewsRelationManagerTest extends TestCase
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
    public function itLoadsForAnEateryWithNoReviews(): void
    {
        $this->reviewsPanel()->assertOk();
    }

    #[Test]
    public function itShowsTheReviewsForTheEatery(): void
    {
        $review = $this->create(EateryReview::class, ['wheretoeat_id' => $this->eatery->id]);

        $this->reviewsPanel()->assertCanSeeTableRecords([$review]);
    }

    #[Test]
    public function itDoesNotShowReviewsFromAnotherEatery(): void
    {
        $mine = $this->create(EateryReview::class, ['wheretoeat_id' => $this->eatery->id]);
        $theirs = $this->create(EateryReview::class, ['wheretoeat_id' => $this->create(Eatery::class)->id]);

        $this->reviewsPanel()
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs]);
    }

    protected function reviewsPanel(): Testable
    {
        return Livewire::test(ReviewsRelationManager::class, [
            'ownerRecord' => $this->eatery,
            'pageClass' => EditEatery::class,
        ]);
    }
}
