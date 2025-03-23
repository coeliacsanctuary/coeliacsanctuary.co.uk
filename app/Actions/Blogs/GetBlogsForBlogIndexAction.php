<?php

declare(strict_types=1);

namespace App\Actions\Blogs;

use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use App\Resources\Blogs\BlogListCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GetBlogsForBlogIndexAction
{
    /**
     * @template T of ResourceCollection
     *
     * @param  class-string<T>  $resource
     * @return T
     */
    public function handle(?BlogTag $tag = null, int $perPage = 12, string $resource = BlogListCollection::class, ?string $search = null): ResourceCollection
    {
        return new $resource(
            Blog::query()
                ->when($tag && $tag->exists, fn (Builder $builder) => $builder->whereHas(
                    'tags',
                    fn (Builder $builder) => $builder->where('slug', $tag->slug), /** @phpstan-ignore-line */
                ))
                ->when($search, fn (Builder $builder) => $builder->where(
                    fn (Builder $builder) => $builder
                        ->where('id', $search)
                        ->orWhere('title', 'LIKE', "%{$search}%")
                ))
                ->with(['media', 'tags'])
                ->withCount(['comments'])
                ->latest()
                ->paginate($perPage)
        );
    }
}
