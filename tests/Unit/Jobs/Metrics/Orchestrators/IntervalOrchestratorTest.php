<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Metrics\Orchestrators;

use App\Jobs\Metrics\Blogs\GetBlogMetricsJob;
use App\Jobs\Metrics\Orchestrators\DailyOrchestrator;
use App\Jobs\Metrics\Orchestrators\HourlyOrchestrator;
use App\Jobs\Metrics\Orchestrators\MinuteOrchestrator;
use App\Jobs\Metrics\Orchestrators\SixHourOrchestrator;
use App\Jobs\Metrics\Orchestrators\ThirtyMinuteOrchestrator;
use App\Jobs\Metrics\Orchestrators\ThreeHourOrchestrator;
use App\Jobs\Metrics\Orchestrators\TenMinuteOrchestrator;
use App\Jobs\Metrics\Orchestrators\TwelveHourOrchestrator;
use App\Jobs\Metrics\Orchestrators\TwoHourOrchestrator;
use App\Models\Blogs\Blog;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IntervalOrchestratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
    }

    /** @return array<string, array{class-string, \Illuminate\Support\Carbon, \Illuminate\Support\Carbon}> */
    public static function orchestratorProvider(): array
    {
        return [
            'minute — blog within last 24h' => [
                MinuteOrchestrator::class,
                now()->subHours(12),
                now()->subHours(23),
            ],
            'ten minute — blog within last week' => [
                TenMinuteOrchestrator::class,
                now()->subDays(3),
                now()->subDays(6),
            ],
            'thirty minute — blog within last two weeks' => [
                ThirtyMinuteOrchestrator::class,
                now()->subDays(10),
                now()->subDays(13),
            ],
            'hourly — blog within last month' => [
                HourlyOrchestrator::class,
                now()->subDays(20),
                now()->subDays(25),
            ],
            'two hour — blog within last two months' => [
                TwoHourOrchestrator::class,
                now()->subWeeks(6),
                now()->subWeeks(7),
            ],
            'three hour — blog within last six months' => [
                ThreeHourOrchestrator::class,
                now()->subMonths(3),
                now()->subMonths(4),
            ],
            'six hour — blog within last year' => [
                SixHourOrchestrator::class,
                now()->subMonths(8),
                now()->subMonths(10),
            ],
            'twelve hour — blog within last two years' => [
                TwelveHourOrchestrator::class,
                now()->subMonths(18),
                now()->subMonths(22),
            ],
            'daily — blog older than two years' => [
                DailyOrchestrator::class,
                now()->subYears(3),
                now()->subYears(4),
            ],
        ];
    }

    /**
     * @param class-string $orchestratorClass
     */
    #[Test]
    #[DataProvider('orchestratorProvider')]
    public function itDispatchesForBlogsInItsAgeRange(
        string $orchestratorClass,
        \Illuminate\Support\Carbon $blogAgeA,
        \Illuminate\Support\Carbon $blogAgeB,
    ): void {
        $this->create(Blog::class, ['created_at' => $blogAgeA]);
        $this->create(Blog::class, ['created_at' => $blogAgeB]);

        (new $orchestratorClass())->handle();

        Bus::assertDispatched(GetBlogMetricsJob::class, 2);
    }

    /** @return array<string, array{class-string, \Illuminate\Support\Carbon}> */
    public static function outOfRangeProvider(): array
    {
        return [
            'minute — blog older than 24h excluded' => [
                MinuteOrchestrator::class,
                now()->subHours(25),
            ],
            'ten minute — blog younger than 24h excluded' => [
                TenMinuteOrchestrator::class,
                now()->subHours(12),
            ],
            'ten minute — blog older than 1 week excluded' => [
                TenMinuteOrchestrator::class,
                now()->subDays(8),
            ],
            'thirty minute — blog younger than 1 week excluded' => [
                ThirtyMinuteOrchestrator::class,
                now()->subDays(3),
            ],
            'thirty minute — blog older than 2 weeks excluded' => [
                ThirtyMinuteOrchestrator::class,
                now()->subDays(16),
            ],
            'hourly — blog younger than 2 weeks excluded' => [
                HourlyOrchestrator::class,
                now()->subDays(10),
            ],
            'hourly — blog older than 1 month excluded' => [
                HourlyOrchestrator::class,
                now()->subDays(40),
            ],
            'daily — blog younger than 2 years excluded' => [
                DailyOrchestrator::class,
                now()->subMonths(20),
            ],
        ];
    }

    /**
     * @param class-string $orchestratorClass
     */
    #[Test]
    #[DataProvider('outOfRangeProvider')]
    public function itDoesNotDispatchForBlogsOutsideItsAgeRange(
        string $orchestratorClass,
        \Illuminate\Support\Carbon $blogAge,
    ): void {
        $this->create(Blog::class, ['created_at' => $blogAge]);

        (new $orchestratorClass())->handle();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }
}
