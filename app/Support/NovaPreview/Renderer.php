<?php

declare(strict_types=1);

namespace App\Support\NovaPreview;

abstract class Renderer
{
    abstract public function component(): string;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    abstract public function payload(array $data): array;
}
