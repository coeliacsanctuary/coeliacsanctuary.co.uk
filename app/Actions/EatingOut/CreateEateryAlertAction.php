<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAlert;

class CreateEateryAlertAction
{
    public function handle(Eatery $eatery, string $type, string $details): ?EateryAlert
    {
        $existingAlert = $eatery->alerts()
            ->pending()
            ->where('type', $type)
            ->exists();

        if ($existingAlert) {
            return null;
        }

        return $eatery->alerts()->create([
            'type' => $type,
            'details' => $details,
            'completed' => false,
            'ignored' => false,
        ]);
    }
}
