<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Metrics\Recipes;

use App\Jobs\Metrics\Recipes\GetRecipeMetricsJob;
use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeMetric;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetRecipeMetricsJobTest extends TestCase
{
    protected Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recipe = $this->create(Recipe::class);

        Http::fake([
            '*' => Http::response([
                'data' => [
                    'views' => 100,
                    'comment_views' => 10,
                    'detail_card_views' => 20,
                    'collection_card_views' => 5,
                ],
            ]),
        ]);
    }

    protected function runJob(?Carbon $date = null): void
    {
        (new GetRecipeMetricsJob($this->recipe, $date ?? today()))->handle();
    }

    #[Test]
    public function itCreatesARecipeMetricForTodayWhenNoneExists(): void
    {
        $this->assertDatabaseEmpty(RecipeMetric::class);

        $this->runJob();

        $this->assertDatabaseCount(RecipeMetric::class, 1);
    }

    #[Test]
    public function itUpdatesAnExistingRecipeMetricForToday(): void
    {
        $this->create(RecipeMetric::class, [
            'recipe_id' => $this->recipe->id,
            'date' => today(),
            'page_views' => 0,
            'page_comment_views' => 0,
            'detail_card_views' => 0,
            'collection_card_views' => 0,
        ]);

        $this->runJob();

        $this->assertDatabaseCount(RecipeMetric::class, 1);

        $metric = RecipeMetric::query()->first();

        $this->assertEquals(100, $metric->page_views);
        $this->assertEquals(10, $metric->page_comment_views);
        $this->assertEquals(20, $metric->detail_card_views);
        $this->assertEquals(5, $metric->collection_card_views);
    }

    #[Test]
    public function itStoresTheCorrectPageViews(): void
    {
        $this->runJob();

        $metric = RecipeMetric::query()->first();

        $this->assertEquals(100, $metric->page_views);
    }

    #[Test]
    public function itStoresTheCorrectPageCommentViews(): void
    {
        $this->runJob();

        $metric = RecipeMetric::query()->first();

        $this->assertEquals(10, $metric->page_comment_views);
    }

    #[Test]
    public function itStoresTheCorrectDetailCardViews(): void
    {
        $this->runJob();

        $metric = RecipeMetric::query()->first();

        $this->assertEquals(20, $metric->detail_card_views);
    }

    #[Test]
    public function itStoresTheCorrectCollectionCardViews(): void
    {
        $this->runJob();

        $metric = RecipeMetric::query()->first();

        $this->assertEquals(5, $metric->collection_card_views);
    }

    #[Test]
    public function itSendsTheCorrectPagePathToJourneyTracker(): void
    {
        $this->runJob();

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $pageDescriptor = collect($data['data'])->firstWhere('as', 'views');

            return $pageDescriptor['has']['pages'][0]['path'] === mb_trim($this->recipe->link, '/');
        });
    }

    #[Test]
    public function itSendsTheCorrectCommentViewsEventToJourneyTracker(): void
    {
        $this->runJob();

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $descriptor = collect($data['data'])->firstWhere('as', 'comment_views');
            $event = $descriptor['has']['events'][0];

            return $event['type'] === 'scrolled_into_view'
                && $event['identifier'] === 'CommentsCard'
                && $event['parameters']['page'] === 'recipe'
                && $event['parameters']['id'] === $this->recipe->id;
        });
    }

    #[Test]
    public function itSendsTheCorrectDetailCardViewsEventToJourneyTracker(): void
    {
        $this->runJob();

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $descriptor = collect($data['data'])->firstWhere('as', 'detail_card_views');
            $event = $descriptor['has']['events'][0];

            return $event['type'] === 'scrolled_into_view'
                && $event['identifier'] === 'RecipeDetailCard'
                && $event['parameters']['title'] === $this->recipe->title;
        });
    }

    #[Test]
    public function itSendsTheCorrectCollectionCardViewsEventToJourneyTracker(): void
    {
        $this->runJob();

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $descriptor = collect($data['data'])->firstWhere('as', 'collection_card_views');
            $event = $descriptor['has']['events'][0];

            return $event['type'] === 'scrolled_into_view'
                && $event['identifier'] === 'CollectionItemCard'
                && $event['parameters']['title'] === $this->recipe->title
                && $event['parameters']['type'] === 'Recipe';
        });
    }

    #[Test]
    public function itWritesMetricsForTheGivenDate(): void
    {
        $date = today()->subDay();

        $this->runJob($date);

        $metric = RecipeMetric::query()->first();

        $this->assertEquals($date->toDateString(), $metric->date->toDateString());
    }
}
