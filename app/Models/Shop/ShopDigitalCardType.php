<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Concerns\DisplaysMedia;
use App\Models\Media;
use App\Scopes\LiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ShopDigitalCardType extends Model implements HasMedia
{
    use DisplaysMedia;

    /** @use InteractsWithMedia<Media> */
    use InteractsWithMedia;

    protected $casts = [
        'live' => 'bool',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new LiveScope);
    }

    public static function fromRequestPath(string $path): self
    {
        return static::query()->where('slug', Str::after($path, 'shop/'))->firstOrFail();
    }

    /** @return HasMany<ShopDigitalCard, $this> */
    public function cards(): HasMany
    {
        return $this->hasMany(ShopDigitalCard::class, 'type_id');
    }

    /** @return BelongsTo<ShopCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ShopCategory::class, 'category_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('social')->singleFile();

        $this->addMediaCollection('primary')->singleFile();
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        if ( ! $media || $media->extension === 'webp') {
            return;
        }

        $this
            ->addMediaConversion('webp')
            ->performOnCollections('primary')
            ->nonQueued()
            ->format('webp');
    }
}
