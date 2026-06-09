<?php

declare(strict_types=1);

namespace App\Resources\Blogs;

use App\Models\Blogs\Blog;
use App\ResourceCollections\Blogs\BlogTagCollection;
use App\Resources\Collections\FeaturedInCollectionSimpleCardViewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/** @mixin Blog */
class BlogShowResource extends JsonResource
{
    /** @return array{id: number, title: string|Stringable, image: string, published: string, updated: string, description: string, body: string|Stringable, hasTwitterEmbed: bool, tags: BlogTagCollection} */
    public function toArray(Request $request)
    {
        $this->load(['associatedCollectionGroups', 'associatedCollectionGroups.group.collection', 'associatedCollectionGroups.group.collection.media']);

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
            'body' => Str::of($this->body)
                ->replace($twitterReplacements, '', false)
                ->replace('&quot;', '"')
                ->markdown([
                    'renderer' => [
                        'soft_break' => '<br />',
                    ],
                ]),
            'hasTwitterEmbed' => Str::contains($this->body, $twitterReplacements),
            'header_image_alt_text' => $this->header_image_alt_text,
            'short_title' => $this->short_title,
            'show_author' => $this->show_author,
            'tags' => new BlogTagCollection($this->tags),
            'featured_in' => FeaturedInCollectionSimpleCardViewResource::collection($this->associatedCollectionGroups),
            'faqs' => $this->faqs ? $this->parseFaqs($this->faqs) : null,
            'faq_display' => $this->faq_display,
        ];
    }

    /**
     * @param  array<int, array{fields: array{question: string, answer: string}}>  $faqs
     * @return Collection<int, array{question: string, answer: string}>
     */
    protected function parseFaqs(array $faqs): Collection
    {
        return collect($faqs)->map(fn ($faq) => $faq['fields']);
    }
}
