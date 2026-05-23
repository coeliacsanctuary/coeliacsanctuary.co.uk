<?php

declare(strict_types=1);

namespace App\Models\Collections;

use App\Concerns\CanBePublished;
use App\Concerns\ClearsCache;
use App\Concerns\DisplaysDates;
use App\Concerns\DisplaysMedia;
use App\Concerns\LinkableModel;
use App\Jobs\OpenGraphImages\CreateCollectionIndexPageOpenGraphImageJob;
use App\Models\Media;
use App\Scopes\LiveScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string $description
 * @property string $meta_tags
 */
class Collection extends Model implements HasMedia
{
    use CanBePublished;
    use ClearsCache;
    use DisplaysDates;
    use DisplaysMedia;

    /** @use InteractsWithMedia<Media> */
    use InteractsWithMedia;

    use LinkableModel;

    protected $with = ['groups', 'groups.items'];

    protected $casts = [
        'display_on_homepage' => 'bool',
        'remove_from_homepage' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new LiveScope());

        static::saved(function (): void {
            if (config('coeliac.generate_og_images') === false) {
                return;
            }

            CreateCollectionIndexPageOpenGraphImageJob::dispatch();
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('social')->singleFile();

        $this->addMediaCollection('primary')->singleFile()->withResponsiveImages();
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

    /** @return HasMany<CollectionGroup, $this> */
    public function groups(): HasMany
    {
        return $this->hasMany(CollectionGroup::class)->orderBy('position');
    }

    protected function linkRoot(): string
    {
        return 'collection';
    }

    /** @return Attribute<string, never> */
    public function description(): Attribute
    {
        return Attribute::get(fn () => $this->long_description);
    }

    /** @return Attribute<string, never> */
    public function metaTags(): Attribute
    {
        return Attribute::get(fn () => $this->meta_keywords);
    }

    protected function cacheKey(): string
    {
        return 'collections';
    }
}
