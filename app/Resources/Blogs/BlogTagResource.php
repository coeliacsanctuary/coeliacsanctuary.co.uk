<?php

declare(strict_types=1);

namespace App\Resources\Blogs;

use App\Models\Blogs\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BlogTag */
class BlogTagResource extends JsonResource
{
    /** @return array{tag: string, slug: string} */
    public function toArray(Request $request)
    {
        return [
            'tag' => $this->tag,
            'slug' => $this->slug,
        ];
    }
}
