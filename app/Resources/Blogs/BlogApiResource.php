<?php

declare(strict_types=1);

namespace App\Resources\Blogs;

use App\Models\Blogs\Blog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/** @mixin Blog */
class BlogApiResource extends JsonResource
{
    /** @return array{id: int, title: string|Stringable, description: string, meta_description: string, link: string, main_image: string, created_at: string} */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'title' => Str::of($this->title)->replace('&quot;', '"'),
            'description' => $this->description,
            'meta_description' => $this->meta_description,
            'link' => route('blog.show', ['blog' => $this]),
            'main_image' => $this->main_image,
            'created_at' => $this->published,
        ];
    }
}
