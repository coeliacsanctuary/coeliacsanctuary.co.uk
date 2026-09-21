<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Blogs;

use App\Actions\Blogs\GetBlogsForBlogIndexAction;
use App\ResourceCollections\Blogs\BlogApiCollection;
use Illuminate\Http\Request;

class IndexController
{
    public function __invoke(Request $request, GetBlogsForBlogIndexAction $getBlogsForBlogIndexAction): BlogApiCollection
    {
        $search = $request->has('search') ? $request->string('search')->toString() : null;

        return $getBlogsForBlogIndexAction->handle(resource: BlogApiCollection::class, search: $search);
    }
}
