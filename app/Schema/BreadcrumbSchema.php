<?php

declare(strict_types=1);

namespace App\Schema;

use App\DataObjects\BreadcrumbItemData;
use Illuminate\Support\Collection;
use Spatie\SchemaOrg\BreadcrumbList;
use Spatie\SchemaOrg\ListItem;
use Spatie\SchemaOrg\Thing;

class BreadcrumbSchema
{
    /** @param Collection<int, BreadcrumbItemData> $items */
    public static function make(Collection $items): BreadcrumbList
    {
        return (new BreadcrumbList())->itemListElement($items->filter()->values()->map(function (BreadcrumbItemData $item, int $index): ListItem {
            $listItem = new ListItem();

            $listItem->position($index + 1)->name($item->title);

            if ($item->url) {
                $listItem->item((new Thing())->url($item->url)->identifier($item->title));
            }

            return $listItem;
        })->toArray());
    }
}
