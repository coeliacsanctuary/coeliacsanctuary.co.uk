<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\HasOpenGraphImageContract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string|null $image_url
 * @property Carbon $updated_at
 */
class OpenGraphImage extends Model implements HasMedia
{
    /** @use InteractsWithMedia<Media> */
    use InteractsWithMedia;

    /**
     * @return MorphTo<HasOpenGraphImageContract, $this>
     */
    public function model(): MorphTo /** @phpstan-ignore-line  */
    {
        return $this->morphTo(); /** @phpstan-ignore-line  */
    }

    /** @return Attribute<string | null, never> */
    public function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->getMedia()->first()?->getUrl());
    }
}
