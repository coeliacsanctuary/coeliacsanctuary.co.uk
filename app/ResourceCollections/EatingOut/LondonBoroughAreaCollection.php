<?php

declare(strict_types=1);

namespace App\ResourceCollections\EatingOut;

use App\Resources\EatingOut\LondonBoroughAreaResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LondonBoroughAreaCollection extends ResourceCollection
{
    public $collects = LondonBoroughAreaResource::class;
}
