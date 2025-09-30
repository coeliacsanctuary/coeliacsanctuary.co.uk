<?php

declare(strict_types=1);

namespace App\Resources\Blogs;

use App\Models\Blogs\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Stringable;

/** @mixin Blog */
class RelatedBlogSimpleCardViewResource extends BlogSimpleCardViewResource
{
    /** @return array{title: string|Stringable, link: string, image: string} */
    public function toArray(Request $request)
    {
        return [
            ...parent::toArray($request),
            'related_tag' => $this->getAttribute('related_tag'),
            'related_tag_url' => $this->getAttribute('related_tag_url'),
        ];
    }
}
