<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use App\Concerns\DisplaysDates;
use App\Concerns\DisplaysMedia;
use App\Concerns\LinkableModel;
use App\Models\Media;
use App\Scopes\LiveScope;
use App\Services\EatingOut\Collection\Builder\QueryBuilder;
use App\Services\EatingOut\Collection\Configuration;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property Configuration $configuration
 */
class EateryCollection extends Model implements HasMedia
{
    use DisplaysDates;
    use DisplaysMedia;

    /** @use InteractsWithMedia<Media> */
    use InteractsWithMedia;

    use LinkableModel;

    protected $table = 'wheretoeat_collections';

    protected $casts = [
        'live' => 'bool',
        'configuration' => Configuration::class,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new LiveScope());

        static::saving(function (self $collection): void {
            $collection->query = new QueryBuilder($collection->configuration)->toSql();
        });

        static::saved(function (): void {
            if (config('coeliac.generate_og_images') === false) {
                return;
            }

            // @todo
            //            CreateBlogIndexPageOpenGraphImageJob::dispatch();
            //            CreateHomePageOpenGraphImageJob::dispatch();
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
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

    protected function linkRoot(): string
    {
        return 'eating-out/collections';
    }
}
