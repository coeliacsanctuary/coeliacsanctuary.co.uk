<?php

declare(strict_types=1);

namespace App\Support\NovaPreview;

use InvalidArgumentException;

class NovaPreviewResolver
{
    public function handle(string $previewable): Renderer
    {
        return match ($previewable) {
            'blog' => new BlogRenderer(),
            default => throw new InvalidArgumentException('Unknown previewable.'),
        };
    }
}
