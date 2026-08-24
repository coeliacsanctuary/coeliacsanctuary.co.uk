<?php

declare(strict_types=1);

namespace App\Schema;

use App\Models\Shop\ShopCategory;
use Illuminate\Support\Collection;
use Spatie\SchemaOrg\ItemList;
use Spatie\SchemaOrg\ListItem;
use Spatie\SchemaOrg\Thing;

class ShopCategoryListSchema
{
    /** @param Collection<int, ShopCategory> $categories */
    public static function make(Collection $categories): ItemList
    {
        return (new ItemList())
            ->name('Coeliac Sanctuary Shop Categories')
            ->numberOfItems($categories->count())
            ->itemListElement($categories->values()->map(fn (ShopCategory $category, int $index): ListItem => (new ListItem())
                ->position($index + 1)
                ->name($category->title)
                ->item((new Thing())
                    ->name($category->title)
                    ->description($category->description)
                    ->url($category->absolute_link)
                    ->image($category->main_image)))->all());
    }
}
