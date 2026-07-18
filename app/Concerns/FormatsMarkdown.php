<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

trait FormatsMarkdown
{
    protected function formatMarkdown(string $string, ?callable $using = null): string|Stringable
    {
        return Str::of($string)
            ->pipe($using ?? fn (Stringable $str) => $str)
            ->replace('&quot;', '"')
            ->markdown([
                'renderer' => [
                    'soft_break' => '<br />',
                ],
            ]);
    }
}
