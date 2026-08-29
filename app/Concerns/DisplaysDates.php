<?php

declare(strict_types=1);

namespace App\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 *
 * @property string $published
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $lastUpdated
 */
trait DisplaysDates
{
    /** @return Attribute<string, never> */
    public function published(): Attribute
    {
        return Attribute::get(function () {
            $date = $this->publish_at ?? $this->created_at;

            if ($date < Carbon::now()->subMonth()) {
                return $date->format('jS F Y');
            }

            return $date->diffForHumans();
        });
    }

    /** @return Attribute<string | null, never> */
    public function lastUpdated(): Attribute
    {
        return Attribute::get(function () {
            $date = $this->publish_at ?? $this->created_at;

            if ($date->clone()->startOfSecond()->equalTo($this->updated_at->clone()->startOfSecond())) {
                return null;
            }

            if ($this->updated_at < Carbon::now()->subMonth()) {
                return $this->updated_at->format('jS F Y');
            }

            return $this->updated_at->diffForHumans();
        });
    }
}
