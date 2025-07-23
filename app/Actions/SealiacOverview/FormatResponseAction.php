<?php

declare(strict_types=1);

namespace App\Actions\SealiacOverview;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class FormatResponseAction
{
    public function handle(string $response): Stringable
    {
        return Str::of($response)
            ->markdown([
                'renderer' => [
                    'soft_break' => '<br />',
                ],
            ])
            ->replaceFirst('<p>', '<p><span class="quote-elem open"><span>&ldquo;</span></span>')
            ->replaceLast('<p>', '<p><span class="quote-elem close"><span>&rdquo;</span></span>');
    }
}
