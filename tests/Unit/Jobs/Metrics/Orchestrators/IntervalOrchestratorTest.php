<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Metrics\Orchestrators;

use App\Jobs\Metrics\Blogs\GetBlogMetricsJob;
use App\Jobs\Metrics\Orchestrators\DailyOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\HourlyOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\MinuteOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\SixHourOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\TenMinuteOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\ThirtyMinuteOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\ThreeHourOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\TwelveHourOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\TwoHourOrchestratorJob;
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
                MinuteOrchestratorJob::class,
                now()->subHours(12),
                now()->subHours(23),
            ],
            'ten minute — blog within last week' => [
                TenMinuteOrchestratorJob::class,
                now()->subDays(3),
                now()->subDays(6),
            ],
            'thirty minute — blog within last two weeks' => [
                ThirtyMinuteOrchestratorJob::class,
                now()->subDays(10),
                now()->subDays(13),
            ],
            'hourly — blog within last month' => [
                HourlyOrchestratorJob::class,
                now()->subDays(20),
                now()->subDays(25),
            ],
            'two hour — blog within last two months' => [
                TwoHourOrchestratorJob::class,
                now()->subWeeks(6),
                now()->subWeeks(7),
            ],
            'three hour — blog within last six months' => [
                ThreeHourOrchestratorJob::class,
                now()->subMonths(3),
                now()->subMonths(4),
            ],
            'six hour — blog within last year' => [
                SixHourOrchestratorJob::class,
                now()->subMonths(8),
                now()->subMonths(10),
            ],
            'twelve hour — blog within last two years' => [
                TwelveHourOrchestratorJob::class,
                now()->subMonths(18),
                now()->subMonths(22),
            ],
            'daily — blog older than two years' => [
                DailyOrchestratorJob::class,
                now()->subYears(3),
                now()->subYears(4),
            ],
        ];
    }

    /**
     * @param  class-string  $orchestratorClass
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
                MinuteOrchestratorJob::class,
                now()->subHours(25),
            ],
            'ten minute — blog younger than 24h excluded' => [
                TenMinuteOrchestratorJob::class,
                now()->subHours(12),
            ],
            'ten minute — blog older than 1 week excluded' => [
                TenMinuteOrchestratorJob::class,
                now()->subDays(8),
            ],
            'thirty minute — blog younger than 1 week excluded' => [
                ThirtyMinuteOrchestratorJob::class,
                now()->subDays(3),
            ],
            'thirty minute — blog older than 2 weeks excluded' => [
                ThirtyMinuteOrchestratorJob::class,
                now()->subDays(16),
            ],
            'hourly — blog younger than 2 weeks excluded' => [
                HourlyOrchestratorJob::class,
                now()->subDays(10),
            ],
            'hourly — blog older than 1 month excluded' => [
                HourlyOrchestratorJob::class,
                now()->subDays(40),
            ],
            'daily — blog younger than 2 years excluded' => [
                DailyOrchestratorJob::class,
                now()->subMonths(20),
            ],
        ];
    }

    /**
     * @param  class-string  $orchestratorClass
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
