<?php

declare(strict_types=1);

namespace App\ResourceCollections\Blogs;

use App\Resources\Blogs\BlogDetailCardViewResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BlogListCollection extends ResourceCollection
{
    public $collects = BlogDetailCardViewResource::class;
}
