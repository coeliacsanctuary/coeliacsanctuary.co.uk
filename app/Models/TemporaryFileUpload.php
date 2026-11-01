<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TemporaryFileUpload extends Model
{
    protected $casts = ['delete_at' => 'datetime'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        self::creating(function (self $model): TemporaryFileUpload {
            $model->id ??= Str::uuid()->toString();

            return $model;
        });
    }
}
