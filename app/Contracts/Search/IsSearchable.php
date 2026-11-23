<?php

declare(strict_types=1);

namespace App\Contracts\Search;

use Algolia\ScoutExtended\Builder;
use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 * @property int $id
 * @method array{_rankingInfo: array{userScore: int, firstMatchedWord: int}} scoutMetadata()
 */
interface IsSearchable
{
    /**
     * @param  string  $query
     * @param  Closure  $callback
     * @return Builder
     */
    public static function search($query = '', $callback = null);
}
