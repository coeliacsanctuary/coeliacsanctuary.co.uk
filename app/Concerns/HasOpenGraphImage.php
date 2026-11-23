<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\OpenGraphImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @template T of Model
 * @mixin Model
 */
trait HasOpenGraphImage
{
    /** @return MorphOne<OpenGraphImage, T> */
    public function openGraphImage(): MorphOne
    {
        return $this->morphOne(OpenGraphImage::class, 'model');
    }
}
