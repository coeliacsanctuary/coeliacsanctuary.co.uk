<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\Search\Steps;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Blogs\Blog;
use App\Pipelines\Search\Steps\PrepareResource;
use App\Resources\Search\SearchableItemResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class PrepareResourceTest extends TestCase
{
    #[Test]
    public function itHydratesABlogAndLoadsTheCorrectRelations(): void
    {
        $this->withBlogs(1);

        /** @var Blog $blogModel */
        $blogModel = Blog::query()->first();

        $paginator = collect([$blogModel])->paginate();

        $closure = function (LengthAwarePaginator $paginator) use ($blogModel): void {
            /** @var LengthAwarePaginator<SearchableItemResource> $results */
            $resourceToCheck = SearchableItemResource::make($blogModel);

            $this->assertEquals($paginator->first()->toArray(request()), $resourceToCheck->toArray(request()));
        };

        app(PrepareResource::class)->handle($paginator, $closure);
    }
}
