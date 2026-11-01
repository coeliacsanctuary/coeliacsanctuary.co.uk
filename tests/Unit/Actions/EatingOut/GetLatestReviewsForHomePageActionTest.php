<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetLatestReviewsForHomepageAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Resources\EatingOut\SimpleReviewResource;
use Carbon\Carbon;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetLatestReviewsForHomePageActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itCanReturnACollectionOfReviews(): void
    {
        $this->assertInstanceOf(AnonymousResourceCollection::class, $this->callAction(GetLatestReviewsForHomepageAction::class));
    }

    #[Test]
    public function itOnlyReturnsTheReviewsAsASimpleReviewResource(): void
    {
        $this->build(EateryReview::class)
            ->count(5)
            ->on($this->create(Eatery::class))
            ->approved()
            ->create();

        $this->callAction(GetLatestReviewsForHomepageAction::class)->each(function ($item): void {
            $this->assertInstanceOf(SimpleReviewResource::class, $item);
        });
    }

    #[Test]
    public function itReturnsEightReviews(): void
    {
        $this->build(EateryReview::class)
            ->count(10)
            ->on($this->create(Eatery::class))
            ->approved()
            ->create();

        $this->assertCount(8, $this->callAction(GetLatestReviewsForHomepageAction::class));
    }

    #[Test]
    public function itReturnsTheLatestReviewsFirst(): void
    {
        $this->build(EateryReview::class)
            ->count(10)
            ->on($this->create(Eatery::class))
            ->approved()
            ->sequence(fn (Sequence $sequence) => [
                'id' => $sequence->index + 1,
                'name' => "Review {$sequence->index}",
                'created_at' => Carbon::now()->subDays($sequence->index),
            ])
            ->create();

        $eateryNames = $this->callAction(GetLatestReviewsForHomepageAction::class)->map(fn (SimpleReviewResource $review) => $review->name);

        $this->assertContains('Review 0', $eateryNames);
        $this->assertContains('Review 1', $eateryNames);
        $this->assertNotContains('Review 9', $eateryNames);
    }

    #[Test]
    public function itDoesntReturnReviewsThatArentLive(): void
    {
        $this->build(EateryReview::class)
            ->count(10)
            ->on($this->create(Eatery::class))
            ->approved()
            ->sequence(fn (Sequence $sequence) => [
                'id' => $sequence->index + 1,
                'name' => "Review {$sequence->index}",
                'approved' => $sequence->index !== 0,
                'created_at' => Carbon::now()->subDays($sequence->index),
            ])
            ->create();

        $eateryNames = $this->callAction(GetLatestReviewsForHomepageAction::class)->map(fn (SimpleReviewResource $review) => $review->name);

        $this->assertNotContains('Review 0', $eateryNames);
        $this->assertContains('Review 2', $eateryNames);
    }

    #[Test]
    public function itCachesTheEateries(): void
    {
        $this->assertFalse(Cache::has(config('coeliac.cacheable.eating-out-reviews.home')));

        $eateries = $this->callAction(GetLatestReviewsForHomepageAction::class);

        $this->assertTrue(Cache::has(config('coeliac.cacheable.eating-out-reviews.home')));
        $this->assertSame($eateries, Cache::get(config('coeliac.cacheable.eating-out-reviews.home')));
    }

    #[Test]
    public function itLoadsTheBlogsFromTheCache(): void
    {
        DB::enableQueryLog();

        $this->callAction(GetLatestReviewsForHomepageAction::class);

        $this->assertCount(1, DB::getQueryLog());

        $this->callAction(GetLatestReviewsForHomepageAction::class);

        $this->assertCount(1, DB::getQueryLog());
    }
}
