<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Shop\Products;

use App\Actions\Shop\GetProductsForProductIndexAction;
use App\ResourceCollections\Shop\ShopProductApiCollection;
use Illuminate\Http\Request;

class IndexController
{
    public function __invoke(Request $request, GetProductsForProductIndexAction $getBlogsForBlogIndexAction): ShopProductApiCollection
    {
        $search = $request->has('search') ? $request->string('search')->toString() : null;

        return $getBlogsForBlogIndexAction->handle(search: $search);
    }
}
