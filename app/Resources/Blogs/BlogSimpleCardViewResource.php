<?php

declare(strict_types=1);

namespace App\Resources\Blogs;

use App\Models\Blogs\Blog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/** @mixin Blog */
class BlogSimpleCardViewResource extends JsonResource
{
    /** @return array{title: string|Stringable, link: string, image: string} */
    public function toArray(Request $request)
    {
        return [
            'title' => Str::of($this->title)->replace('&quot;', '"'),
            'link' => route('blog.show', ['blog' => $this]),
            'image' => $this->main_image,
        ];
    }
}
