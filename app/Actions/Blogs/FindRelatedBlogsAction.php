<?php

declare(strict_types=1);

namespace App\Actions\Blogs;

use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class FindRelatedBlogsAction
{
    /** @return Collection<int, Blog> */
    public function handle(Blog $blog, int $limit = 10): Collection
    {
        /** @var Collection<int, Blog> $relatedBlogs */
        $relatedBlogs = $blog
            ->tags()
            ->with(['blogs' => fn (Relation $query) => $query->where('blogs.id', '!=', $blog->id)->latest()])
            ->get()
            ->each(
                fn (BlogTag $blogTag) => $blogTag->blogs->each(fn (Blog $blog) => $blog
                    ->setAttribute('related_tag', $blogTag->tag)
                    ->setAttribute('related_tag_url', $blogTag->link()))
            )
            ->pluck('blogs')
            ->flatten()
            ->unique('id')
            ->values()
            ->take($limit);

        return $relatedBlogs;
    }
}
