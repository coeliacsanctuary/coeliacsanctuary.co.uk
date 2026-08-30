<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Blogs\Widgets;

use App\Filament\Resources\MainSite\Blogs\Widgets\BlogMetricChart;
use App\Filament\Resources\MainSite\Blogs\Widgets\CollectionCardViewsChart;
use App\Filament\Resources\MainSite\Blogs\Widgets\CommentViewsChart;
use App\Filament\Resources\MainSite\Blogs\Widgets\DetailCardViewsChart;
use App\Filament\Resources\MainSite\Blogs\Widgets\PageViewsChart;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogMetric;
use Carbon\Carbon;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogMetricChartTest extends TestCase
{
    protected Blog $blog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-08-30 12:00:00'));

        $this->blog = $this->create(Blog::class);
    }

    #[Test]
    public function itReturnsADataPointForEveryDayInTheDefaultRange(): void
    {
        $data = $this->chartData(PageViewsChart::class);

        $this->assertCount(15, $data['labels']);
        $this->assertCount(15, $data['datasets'][0]['data']);
        $this->assertSame('16/08', $data['labels'][0]);
        $this->assertSame('30/08', $data['labels'][14]);
    }

    #[Test]
    public function itReturnsTheMetricValueForEachDay(): void
    {
        $this->createMetric('2026-08-28', ['page_views' => 120]);
        $this->createMetric('2026-08-30', ['page_views' => 45]);

        $data = $this->chartData(PageViewsChart::class);

        $this->assertSame(120, $data['datasets'][0]['data'][12]);
        $this->assertSame(45, $data['datasets'][0]['data'][14]);
    }

    #[Test]
    public function itFillsDaysWithoutAMetricWithZero(): void
    {
        $this->createMetric('2026-08-30', ['page_views' => 45]);

        $data = $this->chartData(PageViewsChart::class);

        $this->assertSame(array_fill(0, 14, 0), array_slice($data['datasets'][0]['data'], 0, 14));
    }

    #[Test]
    public function itExcludesMetricsOutsideOfTheRange(): void
    {
        $this->createMetric('2026-08-15', ['page_views' => 999]);

        $data = $this->chartData(PageViewsChart::class);

        $this->assertNotContains(999, $data['datasets'][0]['data']);
    }

    #[Test]
    public function itShortensTheRangeForThePastWeekFilter(): void
    {
        $data = $this->chartData(PageViewsChart::class, 'last7');

        $this->assertCount(8, $data['labels']);
        $this->assertSame('23/08', $data['labels'][0]);
    }

    #[Test]
    public function itLengthensTheRangeForThePastMonthFilter(): void
    {
        $data = $this->chartData(PageViewsChart::class, 'lastMonth');

        $this->assertCount(31, $data['labels']);
        $this->assertSame('31/07', $data['labels'][0]);
    }

    #[Test]
    public function itBucketsThePastYearFilterByMonth(): void
    {
        $data = $this->chartData(PageViewsChart::class, 'lastYear');

        $this->assertCount(13, $data['labels']);
        $this->assertSame('Aug 25', $data['labels'][0]);
        $this->assertSame('Aug 26', $data['labels'][12]);
    }

    #[Test]
    public function itSumsEachMonthForThePastYearFilter(): void
    {
        $this->createMetric('2026-08-01', ['page_views' => 10]);
        $this->createMetric('2026-08-02', ['page_views' => 5]);
        $this->createMetric('2026-08-30', ['page_views' => 7]);

        $data = $this->chartData(PageViewsChart::class, 'lastYear');

        $this->assertSame(22, $data['datasets'][0]['data'][12]);
    }

    #[Test]
    public function itFallsBackToTheDefaultRangeForAnUnknownFilter(): void
    {
        $data = $this->chartData(PageViewsChart::class, 'or 1=1');

        $this->assertCount(15, $data['labels']);
    }

    #[Test]
    #[DataProvider('chartColumns')]
    public function eachChartReadsItsOwnColumn(string $chart, string $column): void
    {
        $this->createMetric('2026-08-30', [$column => 64]);

        $data = $this->chartData($chart);

        $this->assertSame(64, $data['datasets'][0]['data'][14]);
    }

    public static function chartColumns(): array
    {
        return [
            'page views' => [PageViewsChart::class, 'page_views'],
            'comment views' => [CommentViewsChart::class, 'page_comment_views'],
            'detail card views' => [DetailCardViewsChart::class, 'detail_card_views'],
            'collection card views' => [CollectionCardViewsChart::class, 'collection_card_views'],
        ];
    }

    #[Test]
    public function itPinsTheBottomOfTheYAxisToZero(): void
    {
        $scale = $this->chartOptions(PageViewsChart::class)['scales']['y'];

        $this->assertSame(0, $scale['min']);
        $this->assertTrue($scale['beginAtZero']);
    }

    #[Test]
    public function itOnlyLabelsTheYAxisInWholeNumbers(): void
    {
        $scale = $this->chartOptions(PageViewsChart::class)['scales']['y'];

        $this->assertSame(0, $scale['ticks']['precision']);
    }

    #[Test]
    public function itHasNoDescriptionWhenTheBlogHasNoMetrics(): void
    {
        $this->assertNull($this->chart(PageViewsChart::class)->getDescription());
    }

    #[Test]
    public function itDescribesWhenTheMetricsWereLastUpdated(): void
    {
        $this->createMetric('2026-08-30', ['page_views' => 1]);

        $this->assertSame(
            'Last updated: 2026-08-30 12:00:00',
            $this->chart(PageViewsChart::class)->getDescription()
        );
    }

    /** @param class-string<BlogMetricChart> $chart */
    protected function chart(string $chart, ?string $filter = null): BlogMetricChart
    {
        $component = Livewire::test($chart, ['record' => $this->blog]);

        if ($filter !== null) {
            $component->set('filter', $filter);
        }

        $instance = $component->instance();

        $this->assertInstanceOf(BlogMetricChart::class, $instance);

        return $instance;
    }

    /**
     * @param  class-string<BlogMetricChart>  $chart
     * @return array{datasets: array<int, array{label: string, data: array<int, int>}>, labels: array<int, string>}
     */
    protected function chartData(string $chart, ?string $filter = null): array
    {
        return (fn () => $this->getData())->call($this->chart($chart, $filter));
    }

    /**
     * @param  class-string<BlogMetricChart>  $chart
     * @return array<string, mixed>
     */
    protected function chartOptions(string $chart): array
    {
        return (fn () => $this->getOptions())->call($this->chart($chart));
    }

    protected function createMetric(string $date, array $attributes = []): BlogMetric
    {
        return $this->create(BlogMetric::class, ['blog_id' => $this->blog->id, 'date' => $date, ...$attributes]);
    }
}
