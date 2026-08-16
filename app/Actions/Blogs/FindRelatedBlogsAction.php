<?php

declare(strict_types=1);

namespace App\Actions\Blogs;

use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class FindRelatedBlogsAction
{
    /**
     * @param  Collection<int, BlogTag>  $tags
     * @return Collection<int, Blog>
     */
    public function handle(Collection $tags, ?BlogTag $primaryTag = null, ?int $excludeBlogId = null, int $limit = 10): Collection
    {
        $primaryTagBlogs = collect();

        if ($primaryTag) {
            $primaryTagBlogs = $this->loadBlogs(collect([$primaryTag]), $excludeBlogId)
                ->pluck('blogs')
                ->flatten()
                ->take($limit);

            if ($primaryTagBlogs->count() === $limit) {
                return $primaryTagBlogs;
            }
        }

        /** @var Collection<int, Blog> $relatedBlogs */
        $relatedBlogs = $this
            ->loadBlogs($tags->reject(fn (BlogTag $tag): bool => $tag->is($primaryTag)), $excludeBlogId)
            ->pluck('blogs')
            ->when($primaryTagBlogs->isNotEmpty(), fn (Collection $collection) => $primaryTagBlogs->concat($collection))
            ->flatten()
            ->unique('id')
            ->values()
            ->take($limit);

        return $relatedBlogs;
    }

    /**
     * @param  Collection<int, BlogTag>  $tags
     * @return Collection<int, BlogTag>
     */
    protected function loadBlogs(Collection $tags, ?int $excludeBlogId): Collection
    {
        return $tags
            ->each(fn (BlogTag $tag) => $tag->load([
                'blogs' => fn (Relation $query) => $query
                    ->when($excludeBlogId, fn ($query) => $query->where('blogs.id', '!=', $excludeBlogId))
                    ->latest(),
            ]))
            ->each(
                fn (BlogTag $tag) => $tag->blogs->each(fn (Blog $blog) => $blog
                    ->setAttribute('related_tag', $tag->tag)
                    ->setAttribute('related_tag_url', $tag->link()))
            );
    }
}
