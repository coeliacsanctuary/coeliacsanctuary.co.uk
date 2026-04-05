<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use App\Models\EatingOut\EateryCollection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncEateryCollectionAndBlogJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected EateryCollection $collection)
    {
    }

    public function handle(): void
    {
        if ( ! $this->collection->cross_post_to_blogs) {
            if ($this->collection->blog_id) {
                Blog::query()->where('id', $this->collection->blog_id)->delete();
                $this->collection->updateQuietly(['blog_id' => null]);
            }

            return;
        }

        $blog = Blog::query()->updateOrCreate([
            'eatery_collection_id' => $this->collection->id,
        ], [
            'title' => $this->collection->title,
            'slug' => $this->collection->slug,
            'meta_tags' => $this->collection->meta_tags,
            'meta_description' => $this->collection->meta_description,
            'description' => $this->collection->description,
            'draft' => $this->collection->draft,
            'live' => $this->collection->live,
            'body' => '',
        ]);

        $tag = BlogTag::query()->firstOrCreate([
            'tag' => 'Eatery Collection',
            'slug' => 'eatery-collection',
        ]);

        $blog->tags()->sync([$tag]);

        $this->collection->updateQuietly([
            'blog_id' => $blog->id,
        ]);
    }
}
