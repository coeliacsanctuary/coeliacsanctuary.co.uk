<?php

declare(strict_types=1);

namespace App\Enums\EatingOut;

enum TripSummaryStatus: string
{
    case PENDING = 'pending';
    case READY = 'ready';
    case FAILED = 'failed';

    public function name(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::READY => 'Ready',
            self::FAILED => 'Failed',
        };
    }
}
