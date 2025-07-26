<?php

declare(strict_types=1);

namespace Jpeters8889\EateryLocationSearch;

readonly class ResultDto
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
}
