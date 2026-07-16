<?php

namespace App\Concerns;

use Illuminate\Support\Str;

trait FormatsMarkdown {
    protected function formatMarkdown(string $string, ?callable $using = null)
    {
        return Str::of($string)
            ->when($using !== null, fn(Str $str) => $str->tap($using))
            ->replace('&quot;', '"')
            ->markdown([
                'renderer' => [
                    'soft_break' => '<br />',
                ],
            ]);
    }
}
