<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use App\Concerns\CanBePublished;
use App\Concerns\DisplaysDates;
use App\Concerns\DisplaysMedia;
use App\Concerns\LinkableModel;
use App\Jobs\EatingOut\CalculateEateryCollectionEateryCountsJob;
use App\Models\Media;
use App\Scopes\LiveScope;
use App\Services\EatingOut\Collection\Configuration;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\SchemaOrg\Blog as BlogSchema;
use Spatie\SchemaOrg\Schema;

/**
 * @property Configuration $configuration
 */
class EateryCollection extends Model implements HasMedia
{
    use CanBePublished;
    use DisplaysDates;
    use DisplaysMedia;

    /** @use InteractsWithMedia<Media> */
    use InteractsWithMedia;

    use LinkableModel;

    protected $table = 'wheretoeat_collections';

    protected $casts = [
        'draft' => 'bool',
        'live' => 'bool',
        'configuration' => Configuration::class,
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new LiveScope());

        static::saved(function (self $collection): void {
            if (config('coeliac.generate_og_images') === true) {
                // @todo
                //            CreateBlogIndexPageOpenGraphImageJob::dispatch();
                //            CreateHomePageOpenGraphImageJob::dispatch();
            }

            CalculateEateryCollectionEateryCountsJob::dispatch($collection);
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

    public function schema(): BlogSchema
    {
        /** @var string $url */
        $url = config('app.url');

        return Schema::blog()
            ->author(Schema::person()->name('Alison Peters'))
            ->dateModified($this->updated_at)
            ->datePublished($this->created_at)
            ->description($this->meta_description)
            ->headline($this->title)
            ->image($this->main_image)
            ->mainEntityOfPage(Schema::webPage()->identifier($url))
            ->publisher(
                Schema::organization()
                    ->name('Coeliac Sanctuary')
                    ->logo(Schema::imageObject()->url($url . '/images/logo.svg'))
            );
    }
}
