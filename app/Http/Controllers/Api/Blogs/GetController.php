<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Blogs;

use App\Models\Blogs\Blog;
use App\Resources\Blogs\BlogApiResource;

class GetController
{
    public function __invoke(Blog $blog): BlogApiResource
    {
        return BlogApiResource::make($blog);
    }
}
