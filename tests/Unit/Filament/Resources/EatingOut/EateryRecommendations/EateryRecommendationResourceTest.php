<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryRecommendations;

use App\Filament\Resources\EatingOut\EateryRecommendations\EateryRecommendationResource;
use App\Models\EatingOut\EateryRecommendation;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryRecommendationResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itShowsTheNumberOfPendingRecommendationsInTheNavigationBadge(): void
    {
        $this->create(EateryRecommendation::class, 2);
        $this->create(EateryRecommendation::class, ['completed' => true]);
        $this->create(EateryRecommendation::class, ['ignored' => true]);

        $this->assertSame('2', EateryRecommendationResource::getNavigationBadge());
    }

    #[Test]
    public function itDoesNotCountMyPlacesInTheNavigationBadge(): void
    {
        $this->create(EateryRecommendation::class);
        $this->create(EateryRecommendation::class, ['email' => 'alisondwheatley@gmail.com']);

        $this->assertSame('1', EateryRecommendationResource::getNavigationBadge());
    }

    #[Test]
    public function itHasNoNavigationBadgeWhenEveryRecommendationIsCompletedOrIgnored(): void
    {
        $this->create(EateryRecommendation::class, ['completed' => true]);
        $this->create(EateryRecommendation::class, ['ignored' => true]);

        $this->assertNull(EateryRecommendationResource::getNavigationBadge());
    }

    #[Test]
    public function itShowsTheNavigationBadgeInRed(): void
    {
        $this->assertSame('danger', EateryRecommendationResource::getNavigationBadgeColor());
    }

    #[Test]
    public function itRegistersTheListPageOnly(): void
    {
        $this->assertSame(['index'], array_keys(EateryRecommendationResource::getPages()));
    }
}
