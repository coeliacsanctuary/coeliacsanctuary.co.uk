<?php

declare(strict_types=1);

namespace App\ResourceCollections\Blogs;

use App\Resources\Blogs\BlogApiResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BlogApiCollection extends ResourceCollection
{
    public $collects = BlogApiResource::class;

    /** @return array{data: mixed} */
    public function toArray(Request $request)
    {
        return ['data' => parent::toArray($request)];
    }
}
