<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetEateryGuideStatisticsAction;
use App\Enums\EatingOut\EateryType;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetEateryGuideStatisticsActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itReturnsACountForEachEateryType(): void
    {
        $this->build(Eatery::class)->count(3)->create(['type_id' => EateryType::EATERY->value]);
        $this->build(Eatery::class)->count(2)->create(['type_id' => EateryType::ATTRACTION->value]);
        $this->build(Eatery::class)->create(['type_id' => EateryType::HOTEL->value]);

        $statistics = $this->callAction(GetEateryGuideStatisticsAction::class);

        $this->assertSame(3, $statistics['eateries']);
        $this->assertSame(2, $statistics['attractions']);
        $this->assertSame(1, $statistics['hotels']);
    }

    #[Test]
    public function itIncludesNationwideBranches(): void
    {
        $this->build(NationwideBranch::class)->count(4)->create([
            'wheretoeat_id' => $this->create(Eatery::class)->id,
        ]);

        $this->assertSame(4, $this->callAction(GetEateryGuideStatisticsAction::class)['branches']);
    }

    #[Test]
    public function theTotalIncludesBranchesAsWellAsEateries(): void
    {
        $eatery = $this->create(Eatery::class, ['type_id' => EateryType::EATERY->value]);

        $this->build(NationwideBranch::class)->count(2)->create(['wheretoeat_id' => $eatery->id]);

        $statistics = $this->callAction(GetEateryGuideStatisticsAction::class);

        $this->assertSame(3, $statistics['total']);
    }

    #[Test]
    public function itReturnsTheApprovedReviewCount(): void
    {
        $eatery = $this->create(Eatery::class);

        $this->build(EateryReview::class)->approved()->count(5)->create(['wheretoeat_id' => $eatery->id]);

        $this->assertSame(5, $this->callAction(GetEateryGuideStatisticsAction::class)['reviews']);
    }

    #[Test]
    public function itDoesntCountReviewsAwaitingApproval(): void
    {
        $eatery = $this->create(Eatery::class);

        $this->build(EateryReview::class)->count(3)->create(['wheretoeat_id' => $eatery->id]);

        $this->assertSame(0, $this->callAction(GetEateryGuideStatisticsAction::class)['reviews']);
    }

    #[Test]
    public function itDoesntCountEateriesThatArentLive(): void
    {
        $this->build(Eatery::class)->count(2)->create([
            'type_id' => EateryType::EATERY->value,
            'live' => false,
        ]);

        $this->assertSame(0, $this->callAction(GetEateryGuideStatisticsAction::class)['eateries']);
    }

    #[Test]
    public function itCachesTheStatistics(): void
    {
        $this->assertFalse(Cache::has(config('coeliac.cacheable.eating-out.guide-statistics')));

        $statistics = $this->callAction(GetEateryGuideStatisticsAction::class);

        $this->assertTrue(Cache::has(config('coeliac.cacheable.eating-out.guide-statistics')));
        $this->assertSame($statistics, Cache::get(config('coeliac.cacheable.eating-out.guide-statistics')));
    }
}
