<?php

declare(strict_types=1);

namespace App\Support\State\EatingOut\Search;

class SearchResultIdsState
{
    /** @var int[] */
    public static array $eateryIds = [];

    /** @var int[] */
    public static array $branchIds = [];

    public static function reset(): void
    {
        static::$eateryIds = [];
        static::$branchIds = [];
    }
}
