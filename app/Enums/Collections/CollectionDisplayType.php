<?php

declare(strict_types=1);

namespace App\Enums\Collections;

enum CollectionDisplayType: string
{
    case GRID = 'grid';
    case LIST = 'list';

    public function name(): string
    {
        return match ($this) {
            self::GRID => 'Grid',
            self::LIST => 'List',
        };
    }
}
