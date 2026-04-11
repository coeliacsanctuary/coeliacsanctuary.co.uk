<?php

declare(strict_types=1);

namespace App\ResourceCollections\EatingOut;

use App\Resources\EatingOut\EateryCollectionCardResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EateryCollectionListCollection extends ResourceCollection
{
    public $collects = EateryCollectionCardResource::class;
}
