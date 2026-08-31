<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Recipes\Widgets;

use App\Filament\Resources\MainSite\Recipes\Widgets\CollectionCardViewsChart;
use App\Filament\Resources\MainSite\Recipes\Widgets\CommentViewsChart;
use App\Filament\Resources\MainSite\Recipes\Widgets\DetailCardViewsChart;
use App\Filament\Resources\MainSite\Recipes\Widgets\PageViewsChart;
use App\Filament\Resources\MainSite\Recipes\Widgets\RecipeMetricChart;
use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeMetric;
use Carbon\Carbon;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipeMetricChartTest extends TestCase
{
    protected Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-08-30 12:00:00'));

        $this->recipe = $this->create(Recipe::class);
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
    public function itOnlyReadsTheMetricsOfItsOwnRecipe(): void
    {
        $other = $this->create(Recipe::class);

        $this->create(RecipeMetric::class, [
            'recipe_id' => $other->id,
            'date' => '2026-08-30',
            'page_views' => 999,
        ]);

        $data = $this->chartData(PageViewsChart::class);

        $this->assertNotContains(999, $data['datasets'][0]['data']);
    }

    #[Test]
    public function itBucketsThePastYearFilterByMonth(): void
    {
        $this->createMetric('2026-08-01', ['page_views' => 10]);
        $this->createMetric('2026-08-30', ['page_views' => 7]);

        $data = $this->chartData(PageViewsChart::class, 'lastYear');

        $this->assertCount(13, $data['labels']);
        $this->assertSame(17, $data['datasets'][0]['data'][12]);
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
    #[DataProvider('chartHeadings')]
    public function eachChartIsHeaded(string $chart, string $heading): void
    {
        $this->assertSame($heading, $this->chart($chart)->getHeading());
    }

    public static function chartHeadings(): array
    {
        return [
            'page views' => [PageViewsChart::class, 'Views'],
            'comment views' => [CommentViewsChart::class, 'Comment Views'],
            'detail card views' => [DetailCardViewsChart::class, 'Detail Card Views'],
            'collection card views' => [CollectionCardViewsChart::class, 'Collection Card Views'],
        ];
    }

    #[Test]
    public function itHasNoDescriptionWhenTheRecipeHasNoMetrics(): void
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

    /** @param class-string<RecipeMetricChart> $chart */
    protected function chart(string $chart, ?string $filter = null): RecipeMetricChart
    {
        $component = Livewire::test($chart, ['record' => $this->recipe]);

        if ($filter !== null) {
            $component->set('filter', $filter);
        }

        $instance = $component->instance();

        $this->assertInstanceOf(RecipeMetricChart::class, $instance);

        return $instance;
    }

    /**
     * @param  class-string<RecipeMetricChart>  $chart
     * @return array{datasets: array<int, array{label: string, data: array<int, int>}>, labels: array<int, string>}
     */
    protected function chartData(string $chart, ?string $filter = null): array
    {
        return (fn () => $this->getData())->call($this->chart($chart, $filter));
    }

    protected function createMetric(string $date, array $attributes = []): RecipeMetric
    {
        return $this->create(RecipeMetric::class, ['recipe_id' => $this->recipe->id, 'date' => $date, ...$attributes]);
    }
}
