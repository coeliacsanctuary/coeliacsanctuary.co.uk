<?php

declare(strict_types=1);

namespace App\ResourceCollections\Blogs;

use App\Resources\Blogs\BlogTagResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BlogTagCollection extends ResourceCollection
{
    public $collects = BlogTagResource::class;
}
