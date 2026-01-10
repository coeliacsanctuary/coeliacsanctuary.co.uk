<?php

declare(strict_types=1);

namespace App\Actions\Blogs;

use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class FindRelatedBlogsAction
{
    /** @return Collection<int, Blog> */
    public function handle(Blog $blog, int $limit = 10): Collection
    {
        $primaryTag = collect();

        if ($blog->primaryTag) {
            $primaryTag = $blog
                ->primaryTag
                ->load(['blogs' => fn (Relation $query) => $query->where('blogs.id', '!=', $blog->id)->latest()])
                ->blogs
                ->each(
                    fn (Blog $b) => $b
                        ->setAttribute('related_tag', $blog->primaryTag->tag)
                        ->setAttribute('related_tag_url', $blog->primaryTag->link())
                )
                ->take($limit);

            if ($primaryTag->count() === $limit) {
                return $primaryTag;
            }
        }

        /** @var Collection<int, Blog> $relatedBlogs */
        $relatedBlogs = $blog
            ->tags()
            ->when($blog->primary_tag_id, fn (Builder $query) => $query->where('blog_tags.id', '!=', $blog->primary_tag_id))
            ->with(['blogs' => fn (Relation $query) => $query->where('blogs.id', '!=', $blog->id)->latest()])
            ->get()
            ->each(
                fn (BlogTag $blogTag) => $blogTag->blogs->each(fn (Blog $blog) => $blog
                    ->setAttribute('related_tag', $blogTag->tag)
                    ->setAttribute('related_tag_url', $blogTag->link()))
            )
            ->pluck('blogs')
            ->when($primaryTag->isNotEmpty(), fn (Collection $collection) => $collection->prepend(...$primaryTag))
            ->flatten()
            ->unique('id')
            ->values()
            ->take($limit);

        return $relatedBlogs;
    }
}
