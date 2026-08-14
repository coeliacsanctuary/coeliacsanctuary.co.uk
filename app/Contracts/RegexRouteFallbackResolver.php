<?php

declare(strict_types=1);

namespace App\Contracts;

interface RegexRouteFallbackResolver
{
    public function regex(): string;

    public function generateRoutePath(array $parameters = []): string;
}
