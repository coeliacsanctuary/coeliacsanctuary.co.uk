<?php

declare(strict_types=1);

namespace App\ResourceCollections\Shop;

use App\Resources\Shop\ShopProductApiResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ShopProductApiCollection extends ResourceCollection
{
    public $collects = ShopProductApiResource::class;
}
