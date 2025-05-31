<?php

declare(strict_types=1);

namespace App\Support\State\Search;

class SearchState
{
    public static bool $hasGeoSearched = false;

    public static ?float $lat = null;

    public static ?float $lng = null;
}
