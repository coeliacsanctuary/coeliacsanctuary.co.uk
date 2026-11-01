<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Str;

#[Table(name: 'wheretoeat_review_images', keyType: 'string')]
#[WithoutIncrementing]
class EateryReviewImage extends Model
{
    protected static function booted(): void
    {
        self::creating(function (self $model): EateryReviewImage {
            $model->id ??= Str::uuid()->toString();

            return $model;
        });
    }

    /** @return BelongsTo<Eatery, $this> */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'wheretoeat_id', 'id');
    }

    /** @return BelongsTo<EateryReview, $this> */
    public function review(): BelongsTo
    {
        return $this->belongsTo(EateryReview::class, 'wheretoeat_review_id', 'id');
    }

    /** @return Attribute<string, never> */
    public function thumb(): Attribute
    {
        return Attribute::get(fn (): string => $this->imageUrl($this->attributes['thumb']));
    }

    /** @return Attribute<string, never> */
    public function path(): Attribute
    {
        return Attribute::get(fn (): string => $this->imageUrl($this->attributes['path']));
    }

    /** @return Attribute<string, never> */
    public function rawThumb(): Attribute
    {
        return Attribute::get(fn (): mixed => $this->attributes['thumb']);
    }

    /** @return Attribute<string, never> */
    public function rawPath(): Attribute
    {
        return Attribute::get(fn (): mixed => $this->attributes['path']);
    }

    protected function imageUrl(string $file): string
    {
        return app(FilesystemManager::class)->disk('review-images')->url($file);
    }
}
