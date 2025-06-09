<?php

declare(strict_types=1);

namespace App\ResourceCollections\EatingOut;

use App\Resources\EatingOut\EateryListResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EateryListCollection extends ResourceCollection
{
    public $collects = EateryListResource::class;
}
