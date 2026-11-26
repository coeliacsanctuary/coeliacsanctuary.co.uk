<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryReviews\Actions;

use App\Filament\Resources\EatingOut\EateryReviews\Pages\ListEateryReviews;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\SealiacOverview;
use App\Models\User;
use App\Notifications\EatingOut\EateryReviewApprovedNotification;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Action;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApproveReviewActionTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itIsVisibleForAnUnapprovedReview(): void
    {
        $review = $this->build(EateryReview::class)->on($this->eatery)->create();

        Livewire::test(ListEateryReviews::class)->assertActionVisible(TestAction::make('approve')->table($review));
    }

    #[Test]
    public function itIsHiddenForAnApprovedReview(): void
    {
        $review = $this->build(EateryReview::class)->on($this->eatery)->approved()->create();

        Livewire::test(ListEateryReviews::class)->assertActionHidden(TestAction::make('approve')->table($review));
    }

    #[Test]
    public function itConfirmsBeforeApproving(): void
    {
        $review = $this->build(EateryReview::class)->on($this->eatery)->create();

        Livewire::test(ListEateryReviews::class)->assertActionExists(
            TestAction::make('approve')->table($review),
            fn (Action $action): bool => $action->isConfirmationRequired(),
        );
    }

    #[Test]
    public function itApprovesTheReview(): void
    {
        $review = $this->build(EateryReview::class)->on($this->eatery)->create();

        $this->approve($review);

        $this->assertTrue($review->refresh()->approved);
    }

    #[Test]
    public function itEmailsTheReviewer(): void
    {
        $review = $this->build(EateryReview::class)->on($this->eatery)->create();

        $this->approve($review);

        Notification::assertSentTo(new AnonymousNotifiable(), EateryReviewApprovedNotification::class);
    }

    #[Test]
    public function itInvalidatesTheEaterysSealiacOverview(): void
    {
        $overview = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();
        $review = $this->build(EateryReview::class)->on($this->eatery)->create();

        $this->approve($review);

        $this->assertTrue($overview->refresh()->invalidated);
    }

    #[Test]
    public function itTouchesTheEatery(): void
    {
        $this->eatery->updateQuietly(['updated_at' => now()->subWeek()]);
        $review = $this->build(EateryReview::class)->on($this->eatery)->create();

        $this->approve($review);

        $this->assertTrue($this->eatery->refresh()->updated_at->isToday());
    }

    protected function approve(EateryReview $review): void
    {
        Livewire::test(ListEateryReviews::class)
            ->callAction(TestAction::make('approve')->table($review))
            ->assertNotified();
    }
}
