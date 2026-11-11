<?php

declare(strict_types=1);

namespace App\Resources\Blogs;

use App\Concerns\FormatsMarkdown;
use App\Models\Blogs\Blog;
use App\ResourceCollections\Blogs\BlogTagCollection;
use App\Resources\Collections\FeaturedInCollectionSimpleCardViewResource;
use App\Resources\Faqs\FaqResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/** @mixin Blog */
class BlogShowResource extends JsonResource
{
    use FormatsMarkdown;

    /** @return array{id: number, title: string|Stringable, image: string, published: string, updated: string|null, description: string, body: string|Stringable, reading_time: int, comments_count: int, hasTwitterEmbed: bool, tags: BlogTagCollection} */
    public function toArray(Request $request)
    {
        $this->load(['associatedCollectionGroups', 'associatedCollectionGroups.group.collection', 'associatedCollectionGroups.group.collection.media', 'faqs']);

        $this->loadCount('comments');

        $twitterReplacements = [
            '<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>',
            '<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>',
        ];

        return [
            'id' => $this->id,
            'title' => Str::of($this->title)->replace('&quot;', '"'),
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'published' => $this->published,
            'updated' => $this->lastUpdated,
            'description' => $this->description,
            'body' => $this->formatMarkdown(
                $this->body,
                fn (Stringable $str): Stringable => $str->replace($twitterReplacements, '', false),
            ),
            'reading_time' => $this->reading_time,
            'comments_count' => $this->comments_count,
            'hasTwitterEmbed' => Str::contains($this->body, $twitterReplacements),
            'header_image_alt_text' => $this->header_image_alt_text,
            'short_title' => $this->short_title,
            'show_author' => $this->show_author,
            'tags' => new BlogTagCollection($this->tags),
            'featured_in' => FeaturedInCollectionSimpleCardViewResource::collection($this->associatedCollectionGroups),
            'faqs' => $this->faqs->isNotEmpty() ? FaqResource::collection($this->faqs) : null,
            'faq_display' => $this->faq_display,
        ];
    }
}
