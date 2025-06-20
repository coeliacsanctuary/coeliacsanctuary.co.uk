<?php

declare(strict_types=1);

namespace App\ResourceCollections\EatingOut;

use App\Resources\EatingOut\LondonBoroughResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LondonBoroughCollection extends ResourceCollection
{
    public $collects = LondonBoroughResource::class;
}
