<?php

declare(strict_types=1);

namespace App\DataObjects\EatingOut;

class PendingEatery
{
    public function __construct(
        public int $id,
        public ?int $branchId,
        public string|float $ordering,
        public null|int|float $lat = null,
        public null|int|float $lng = null,
        public null|int|float $distance = null,
        public ?int $typeId = null,
        public ?int $venueTypeId = null,
    ) {
    }
}
