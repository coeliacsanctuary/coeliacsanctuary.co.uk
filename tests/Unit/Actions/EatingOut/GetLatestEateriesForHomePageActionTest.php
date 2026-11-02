<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetLatestEateriesForHomepageAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Resources\EatingOut\SimpleEateryResource;
use Carbon\Carbon;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetLatestEateriesForHomePageActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itCanReturnACollectionOfEateries(): void
    {
        $this->assertInstanceOf(AnonymousResourceCollection::class, $this->callAction(GetLatestEateriesForHomepageAction::class));
    }

    #[Test]
    public function itOnlyReturnsTheEateriesAsASimpleEateryResource(): void
    {
        $this->build(Eatery::class)->count(5)->create();

        $this->callAction(GetLatestEateriesForHomepageAction::class)->each(function ($item): void {
            $this->assertInstanceOf(SimpleEateryResource::class, $item);
        });
    }

    #[Test]
    public function itReturnsEightEateries(): void
    {
        $this->build(Eatery::class)->count(10)->create();

        $this->assertCount(8, $this->callAction(GetLatestEateriesForHomepageAction::class));
    }

    #[Test]
    public function itReturnsTheLatestEateriesFirst(): void
    {
        $this->build(Eatery::class)
            ->count(10)
            ->sequence(fn (Sequence $sequence): array => [
                'id' => $sequence->index + 1,
                'name' => "Eatery {$sequence->index}",
                'created_at' => Carbon::now()->subDays($sequence->index),
            ])
            ->create();

        $eateryNames = $this->callAction(GetLatestEateriesForHomepageAction::class)->map(fn (SimpleEateryResource $eatery) => $eatery->name);

        $this->assertContains('Eatery 0', $eateryNames);
        $this->assertContains('Eatery 1', $eateryNames);
        $this->assertNotContains('Eatery 9', $eateryNames);
    }

    #[Test]
    public function itDoesntReturnEateriesThatArentLive(): void
    {
        $this->build(Eatery::class)
            ->count(10)
            ->sequence(fn (Sequence $sequence): array => [
                'live' => $sequence->index !== 0,
                'id' => $sequence->index + 1,
                'name' => "Eatery {$sequence->index}",
                'created_at' => Carbon::now()->subDays($sequence->index),
            ])
            ->create();

        $eateryNames = $this->callAction(GetLatestEateriesForHomepageAction::class)->map(fn (SimpleEateryResource $eatery) => $eatery->name);

        $this->assertNotContains('Eatery 0', $eateryNames);
        $this->assertContains('Eatery 2', $eateryNames);
    }

    #[Test]
    public function itAlsoReturnsNationwideBranches(): void
    {
        $this->build(NationwideBranch::class)->count(5)->create([
            'wheretoeat_id' => $this->create(Eatery::class)->id,
        ]);

        $this->callAction(GetLatestEateriesForHomepageAction::class)->each(function ($item): void {
            $this->assertInstanceOf(SimpleEateryResource::class, $item);
        });
    }

    #[Test]
    public function itCachesTheEateries(): void
    {
        $this->assertFalse(Cache::has(config('coeliac.cacheable.eating-out.home')));

        $eateries = $this->callAction(GetLatestEateriesForHomepageAction::class);

        $this->assertTrue(Cache::has(config('coeliac.cacheable.eating-out.home')));
        $this->assertSame($eateries, Cache::get(config('coeliac.cacheable.eating-out.home')));
    }

    #[Test]
    public function itLoadsTheBlogsFromTheCache(): void
    {
        DB::enableQueryLog();

        $this->callAction(GetLatestEateriesForHomepageAction::class);

        $this->assertCount(2, DB::getQueryLog());

        $this->callAction(GetLatestEateriesForHomepageAction::class);

        $this->assertCount(2, DB::getQueryLog());
    }
}
