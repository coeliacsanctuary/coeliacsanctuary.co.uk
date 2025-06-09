<?php

declare(strict_types=1);

namespace App\ResourceCollections\EatingOut;

use App\Resources\EatingOut\CountyTownResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CountyTownCollection extends ResourceCollection
{
    public $collects = CountyTownResource::class;
}
