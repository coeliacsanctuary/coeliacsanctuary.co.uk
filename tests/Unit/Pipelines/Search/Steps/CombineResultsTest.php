<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\Search\Steps;

use App\DataObjects\Search\SearchParameters;
use App\DataObjects\Search\SearchPipelineData;
use App\DataObjects\Search\SearchResultItem;
use App\DataObjects\Search\SearchResultsCollection;
use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\Recipes\Recipe;
use App\Models\Shop\ShopProduct;
use App\Pipelines\Search\Steps\CombineResults;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CombineResultsTest extends TestCase
{
    #[Test]
    public function itCombinesAllSearchResultsIntoOneCollection(): void
    {
        $blog = new SearchResultItem(
            id: 1,
            model: Blog::class,
            score: 123,
            firstWordPosition: 2
        );

        $recipe = new SearchResultItem(
            id: 1,
            model: Recipe::class,
            score: 123,
            firstWordPosition: 2
        );

        $eatery = new SearchResultItem(
            id: 1,
            model: Eatery::class,
            score: 123,
            firstWordPosition: 2
        );

        $product = new SearchResultItem(
            id: 1,
            model: ShopProduct::class,
            score: 123,
            firstWordPosition: 2
        );

        $pipelineData = new SearchPipelineData(
            new SearchParameters('foo'),
            new SearchResultsCollection(
                blogs: collect([$blog]),
                recipes: collect([$recipe]),
                eateries: collect([$eatery]),
                shop: collect([$product]),
            ),
        );

        $closure = function (Collection $results): void {
            $this->assertCount(4, $results);
        };

        app(CombineResults::class)->handle($pipelineData, $closure);
    }
}
