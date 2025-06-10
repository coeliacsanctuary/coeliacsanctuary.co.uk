<?php

declare(strict_types=1);

namespace App\ResourceCollections\Collections;

use App\Resources\Collections\CollectedItemResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CollectedItemCollection extends ResourceCollection
{
    public $collects = CollectedItemResource::class;
}
