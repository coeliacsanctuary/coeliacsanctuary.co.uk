<?php

declare(strict_types=1);

namespace App\Filament\Transformers;

class StatusTransformer
{
    public static function transform(array $data): array
    {
        $data['live'] = match ($data['status']) {
            'Live' => true,
            default => false,
        };

        unset($data['status']);

        return $data;
    }
}
