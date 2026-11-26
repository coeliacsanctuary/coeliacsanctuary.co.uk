<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryReviews;

use App\Filament\Resources\EatingOut\EateryReviews\EateryReviewResource;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryReviewResourceTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itShowsTheNumberOfUnapprovedReviewsInTheNavigationBadge(): void
    {
        $this->build(EateryReview::class)->on($this->eatery)->count(2)->create();
        $this->build(EateryReview::class)->on($this->eatery)->approved()->create();

        $this->assertSame('2', EateryReviewResource::getNavigationBadge());
    }

    #[Test]
    public function itHasNoNavigationBadgeWhenEveryReviewIsApproved(): void
    {
        $this->build(EateryReview::class)->on($this->eatery)->approved()->create();

        $this->assertNull(EateryReviewResource::getNavigationBadge());
    }

    #[Test]
    public function itShowsTheNavigationBadgeInRed(): void
    {
        $this->assertSame('danger', EateryReviewResource::getNavigationBadgeColor());
    }

    #[Test]
    public function itRegistersTheListPageOnly(): void
    {
        $this->assertSame(['index'], array_keys(EateryReviewResource::getPages()));
    }
}
