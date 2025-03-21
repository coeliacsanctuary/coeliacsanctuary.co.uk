<?php

declare(strict_types=1);

namespace App\Services;

use Spatie\Browsershot\Browsershot;

class RenderOpenGraphImage
{
    public function __construct(protected Browsershot $browsershot)
    {
    }

    public function handle(string $html): string
    {
        /** @var string $nodeBinary */
        $nodeBinary = config('browsershot.node_path');

        /** @var string $npmBinary */
        $npmBinary = config('browsershot.npm_path');

        return $this->browsershot
            ->setHtml($html)
            ->setIncludePath('$PATH')
            ->setNodeBinary($nodeBinary)
            ->setNpmBinary($npmBinary)
            ->windowSize(1200, 630)
            ->noSandbox()
            ->base64Screenshot();
    }
}
