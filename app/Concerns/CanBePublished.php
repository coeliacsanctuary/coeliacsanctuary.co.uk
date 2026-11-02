<?php

declare(strict_types=1);

namespace App\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 * @property Carbon $publish_at
 * @property bool $draft
 */
trait CanBePublished
{
    public static function bootCanBePublished(): void
    {
        $casts = [
            'publish_at' => 'datetime',
            'draft' => 'bool',
            'live' => 'bool',
        ];

        static::saved(function (self $model) use ($casts): void {
            $model->mergeCasts($casts);
        });

        static::retrieved(function (self $model) use ($casts): void {
            $model->mergeCasts($casts);
        });
    }
}
