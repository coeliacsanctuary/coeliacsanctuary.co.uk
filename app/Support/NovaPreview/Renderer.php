<?php

namespace App\Support\NovaPreview;

abstract class Renderer {
    abstract public function component(): string;

    abstract public function payload(array $data): array;
}
