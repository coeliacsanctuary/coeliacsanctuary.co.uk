<?php

declare(strict_types=1);

namespace App\ResourceCollections\EatingOut;

use App\Resources\EatingOut\NationwideListResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NationwideListCollection extends ResourceCollection
{
    public $collects = NationwideListResource::class;
}
