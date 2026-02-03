<?php

declare(strict_types=1);

namespace App\DataObjects\EatingOut;

readonly class EateryWebsiteCheckResult
{
    public function __construct(
        public bool $success,
        public ?int $statusCode = null,
        public ?string $errorMessage = null,
        public bool $timedOut = false,
    ) {
        //
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
