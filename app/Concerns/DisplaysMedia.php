<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\Media;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

/**
 * @property string $main_image
 * @property string $main_image_as_webp
 * @property string $social_image
 * @property string $square_image
 * @property string $square_image_as_webp
 * @property string $first_image
 *
 * @mixin HasMedia
 */
trait DisplaysMedia
{
    /** @return Attribute<string | null, never> */
    public function firstImage(): Attribute
    {
        return Attribute::get(function () {
            /** @var MediaCollection<int, Media> $collection */
            $collection = $this->getMedia();

            return $collection->first()?->getUrl();
        });
    }

    /**
     * @return Attribute<string, never>
     *
     * @throws Exception
     */
    public function mainImage(): Attribute
    {
        return Attribute::get(function () {
            /** @var MediaCollection<int, Media> $collection */
            $collection = $this->getMedia('primary');

            /** @var Media $item */
            $item = $collection->first();

            return $item->getUrl();
        });
    }

    /**
     * @return Attribute<string, never>
     *
     * @throws Exception
     */
    public function mainImageAsWebp(): Attribute
    {
        return Attribute::get(function () {
            /** @var MediaCollection<int, Media> $collection */
            $collection = $this->getMedia('primary');

            /** @var Media $item */
            $item = $collection->first();

            return $item->getUrl('webp');
        });
    }

    /** @return Attribute<string, never> */
    public function socialImage(): Attribute
    {
        return Attribute::get(function () {
            /** @var MediaCollection<int, Media> $collection */
            $collection = $this->getMedia('social');

            /** @var Media $item */
            $item = $collection->first();

            return $item->getUrl();
        });
    }

    /** @return Attribute<string | null, never> */
    public function squareImage(): Attribute
    {
        return Attribute::get(function () {
            /** @var MediaCollection<int, Media> $collection */
            $collection = $this->getMedia('square');

            return isset($collection[0]) ? $collection[0]->getUrl() : null;
        });
    }

    /** @return Attribute<string | null, never> */
    public function squareImageAsWebp(): Attribute
    {
        return Attribute::get(function () {
            /** @var MediaCollection<int, Media> $collection */
            $collection = $this->getMedia('square');

            return isset($collection[0]) ? $collection[0]->getUrl('webp') : null;
        });
    }
}
