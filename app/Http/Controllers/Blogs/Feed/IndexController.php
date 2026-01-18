<?php

declare(strict_types=1);

namespace App\Http\Controllers\Blogs\Feed;

use App\Actions\Blogs\GetBlogsForBlogIndexAction;
use App\Feeds\BlogFeed;
use App\Models\Blogs\Blog;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class IndexController
{
    public function __invoke(GetBlogsForBlogIndexAction $getBlogsForBlogIndexAction, BlogFeed $blogFeed): Response
    {
        /** @var Collection<int, Blog> $blogs */
        $blogs = $getBlogsForBlogIndexAction
            ->handle()
            ->collection
            ?->map(fn (JsonResource $resource) => $resource->resource);

        return new Response(
            $blogFeed->render($blogs),
            Response::HTTP_OK,
            ['Content-Type' => 'text/xml'],
        );
    }
}
