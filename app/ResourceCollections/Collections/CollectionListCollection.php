<?php

declare(strict_types=1);

namespace App\ResourceCollections\Collections;

use App\Resources\Collections\CollectionDetailCardViewResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CollectionListCollection extends ResourceCollection
{
    public $collects = CollectionDetailCardViewResource::class;
}
