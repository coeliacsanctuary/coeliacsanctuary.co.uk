<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\Blogs;

use App\Http\Api\V1\Resources\Blogs\BlogResource;
use App\Models\Blogs\Blog;

class IndexController
{
    public function __invoke(): array
    {
        $blogs = Blog::query()
            ->with(['media'])
            ->latest()
            ->paginate(12);

        return [
            'data' => BlogResource::collection($blogs),
        ];
    }
}
