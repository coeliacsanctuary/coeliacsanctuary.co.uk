<?php

declare(strict_types=1);

namespace App\Schema;

use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use Illuminate\Support\Collection;
use Spatie\SchemaOrg\ItemList;
use Spatie\SchemaOrg\ListItem;
use Spatie\SchemaOrg\Thing;

class ShopCategoryProductListSchema
{
    /** @param Collection<int, ShopProduct> $products */
    public static function make(ShopCategory $category, Collection $products): ItemList
    {
        return (new ItemList())
            ->name($category->title)
            ->numberOfItems($products->count())
            ->itemListElement($products->values()->map(fn (ShopProduct $product, int $index): ListItem => (new ListItem())
                ->position($index + 1)
                ->name($product->title)
                ->item((new Thing())
                    ->name($product->title)
                    ->description($product->description)
                    ->url($product->absolute_link)
                    ->image($product->main_image)))->all());
    }
}
