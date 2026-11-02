<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\OpenGraphImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @template T of Model
 * @mixin Model
 */
interface HasOpenGraphImageContract
{
    /** @return MorphOne<OpenGraphImage, T> */
    public function openGraphImage(): MorphOne;
}
