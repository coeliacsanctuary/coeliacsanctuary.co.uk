<?php

declare(strict_types=1);

namespace App\Filament\Dto;

use JsonSerializable;

readonly class EateryLocationDto implements JsonSerializable
{
    public function __construct(
        public string $type,
        public string $label,
        public string $matchedTerm,
        public int $countryId,
        public int $countyId,
        public ?int $townId,
        public ?int $areaId = null,
    ) {
        //
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }

    public function fromArray(array $object): self
    {
        return new self(...$object);
    }
}
