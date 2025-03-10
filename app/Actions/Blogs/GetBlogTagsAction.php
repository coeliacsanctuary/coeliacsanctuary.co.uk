<?php

declare(strict_types=1);

namespace App\Actions\Blogs;

use App\Models\Blogs\BlogTag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetBlogTagsAction
{
    /** @return Collection<int, BlogTag> */
    public function handle(): Collection
    {
        return Cache::rememberForever(
            config('coeliac.cacheable.blogs.tags'),
            fn () => BlogTag::query()
                ->withCount(['blogs'])
                ->orderByDesc('blogs_count')
                ->get(['tag', 'slug', 'blogs_count']),
        );
    }
}
