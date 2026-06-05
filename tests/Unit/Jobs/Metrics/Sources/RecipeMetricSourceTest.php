<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Metrics\Sources;

use App\Jobs\Metrics\Recipes\GetRecipeMetricsJob;
use App\Metrics\Sources\RecipeMetricSource;
use App\Models\Recipes\Recipe;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipeMetricSourceTest extends TestCase
{
    #[Test]
    public function itReturnsOnlyRecipesNewerThanCreatedAfter(): void
    {
        $included = $this->create(Recipe::class, ['created_at' => now()->subDays(3)]);
        $this->create(Recipe::class, ['created_at' => now()->subDays(10)]);

        $source = new RecipeMetricSource(createdAfter: now()->subWeek());

        $results = $source->query()->pluck('id');

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($included->id));
    }

    #[Test]
    public function itReturnsOnlyRecipesOlderThanCreatedBefore(): void
    {
        $this->create(Recipe::class, ['created_at' => now()->subHours(12)]);
        $included = $this->create(Recipe::class, ['created_at' => now()->subDays(3)]);

        $source = new RecipeMetricSource(createdBefore: now()->subHours(24));

        $results = $source->query()->pluck('id');

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($included->id));
    }

    #[Test]
    public function itReturnsRecipesWithinBothBounds(): void
    {
        $this->create(Recipe::class, ['created_at' => now()->subHours(12)]);
        $included = $this->create(Recipe::class, ['created_at' => now()->subDays(3)]);
        $this->create(Recipe::class, ['created_at' => now()->subDays(10)]);

        $source = new RecipeMetricSource(
            createdAfter: now()->subWeek(),
            createdBefore: now()->subHours(24),
        );

        $results = $source->query()->pluck('id');

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($included->id));
    }

    #[Test]
    public function itReturnsAllRecipesWhenNoBoundsSet(): void
    {
        $this->create(Recipe::class);
        $this->create(Recipe::class);

        $source = new RecipeMetricSource();

        $this->assertCount(2, $source->query()->get());
    }

    #[Test]
    public function metricsRelationReturnMetrics(): void
    {
        $source = new RecipeMetricSource();

        $this->assertEquals('metrics', $source->metricsRelation());
    }

    #[Test]
    public function itDispatchesGetRecipeMetricsJobWithCorrectDelay(): void
    {
        Bus::fake();

        $recipe = $this->create(Recipe::class);
        $source = new RecipeMetricSource();

        $source->dispatch($recipe, 30, today());

        Bus::assertDispatched(GetRecipeMetricsJob::class, fn (GetRecipeMetricsJob $job) => $job->delay === 30 && $job->date->isToday());
    }
}
