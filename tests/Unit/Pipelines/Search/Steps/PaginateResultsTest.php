<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\Search\Steps;

use App\DataObjects\Search\SearchResultItem;
use App\Models\Blogs\Blog;
use App\Models\Recipes\Recipe;
use App\Pipelines\Search\Steps\PaginateResults;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaginateResultsTest extends TestCase
{
    #[Test]
    public function itSortsTheSearchResultsByScoreDesc(): void
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
            score: 42,
            firstWordPosition: 2
        );

        $results = collect([
            $blog,
            $recipe,
        ]);

        $closure = function (LengthAwarePaginator $paginator) use ($results): void {
            /** @var LengthAwarePaginator<int, SearchResultItem> $results */
            $this->assertEquals($results->toArray(), $paginator->items());
        };

        app(PaginateResults::class)->handle($results, $closure);
    }
}
