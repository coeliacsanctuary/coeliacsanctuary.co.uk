<?php

declare(strict_types=1);

namespace App\Http\Controllers\Blogs\AllTags;

use App\Http\Response\Inertia;
use App\Models\Blogs\BlogTag;
use Illuminate\Support\Collection;
use Inertia\Response;

class IndexController
{
    public function __invoke(Inertia $inertia): Response
    {
        return $inertia
            ->title('All blog Tags')
            ->doNotTrack()
            ->render('Blog/AllTags', [
                'tags' => BlogTag::query()
                    ->withCount('blogs')
                    ->orderBy('tag')
                    ->get()
                    ->map(fn (BlogTag $tag) => [
                        'tag' => $tag->tag,
                        'blogs' => $tag->blogs_count,
                        'link' => route('blog.index.tags', $tag->slug),
                    ])
                    ->groupBy(fn (array $tag) => $tag['tag'][0])
                    ->map(fn (Collection $tags, string $letter) => [
                        'group' => $letter,
                        'tags' => $tags,
                    ])
                    ->values(),
            ]);
    }
}
