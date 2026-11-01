<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[WithoutIncrementing]
class TemporaryFileUpload extends Model
{
    protected $keyType = 'string';

    protected static function booted(): void
    {
        self::creating(function (self $model): TemporaryFileUpload {
            $model->id ??= Str::uuid()->toString();

            return $model;
        });
    }

    protected function casts(): array
    {
        return ['delete_at' => 'datetime'];
    }
}
