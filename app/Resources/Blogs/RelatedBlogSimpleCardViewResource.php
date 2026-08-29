<?php

declare(strict_types=1);

namespace App\Resources\Blogs;

use App\Models\Blogs\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Stringable;

/** @mixin Blog */
class RelatedBlogSimpleCardViewResource extends BlogSimpleCardViewResource
{
    /** @return array{type: string, title: string|Stringable, link: string, image: string, header_image_alt_text: string|null, date: string, related_tag: string, related_tag_url: string} */
    public function toArray(Request $request)
    {
        return [
            ...parent::toArray($request),
            'related_tag' => (string) $this->getAttribute('related_tag'),
            'related_tag_url' => (string) $this->getAttribute('related_tag_url'),
        ];
    }
}
