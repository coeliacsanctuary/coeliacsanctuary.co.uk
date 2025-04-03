<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\DisplaysMedia;
use App\Legacy\HasLegacyImage;
use App\Legacy\Imageable;
use App\Scopes\LiveScope;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

/**
 * @property string $secondary_image
 */
class Popup extends Model implements HasMedia
{
    use DisplaysMedia;
    use HasLegacyImage;
    use Imageable;
    /** @use InteractsWithMedia<Media> */
    use InteractsWithMedia;

    protected static function booted(): void
    {
        static::addGlobalScope(new LiveScope());
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('primary')->singleFile();
        $this->addMediaCollection('secondary')->singleFile();
    }

    /**
     * @return Attribute<string, never>
     *
     * @throws Exception
     */
    public function secondaryImage(): Attribute
    {
        return Attribute::get(function () {
            /** @var MediaCollection<int, Media> $collection */
            $collection = $this->getMedia('secondary');

            /** @var Media $item */
            $item = $collection->first();

            return $item->getUrl();
        });
    }
}
