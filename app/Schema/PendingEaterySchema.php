<?php

declare(strict_types=1);

namespace App\Schema;

use App\DataObjects\EatingOut\PendingEatery;
use Illuminate\Support\Collection;
use Spatie\SchemaOrg\ItemList;
use Spatie\SchemaOrg\ListItem;
use Spatie\SchemaOrg\LocalBusiness;
use Spatie\SchemaOrg\PostalAddress;

class PendingEaterySchema
{
    /** @param Collection<int, PendingEatery> $eateries */
    public static function make(Collection $eateries, string $town): ItemList
    {
        /** @var Collection<int, ListItem> $schemaItems */
        $schemaItems = $eateries->map(
            fn (PendingEatery $eatery, int $index) => (new ListItem())
                ->position($index + 1)
                ->item((new LocalBusiness())->name((string) $eatery->ordering)->address((new PostalAddress())->addressLocality($town)))
        );

        return (new ItemList())->name("Gluten Free places to eat in {$town}")->itemListElement($schemaItems->toArray());
    }
}
