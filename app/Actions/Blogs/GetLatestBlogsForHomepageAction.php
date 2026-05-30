<?php

declare(strict_types=1);

namespace App\Actions\Blogs;

use App\Models\Blogs\Blog;
use App\Resources\Blogs\BlogSimpleCardViewResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GetLatestBlogsForHomepageAction
{
    public function handle(): AnonymousResourceCollection
    {
        /** @var string $key */
        $key = config('coeliac.cacheable.blogs.home');

        /** @var AnonymousResourceCollection $blogs */
        $blogs = Cache::rememberForever(
            $key,
            fn () => BlogSimpleCardViewResource::collection(Blog::query()
                ->take(6)
                ->latest()
                ->with(['media'])
                ->get())
        );

        return $blogs;
    }
}
