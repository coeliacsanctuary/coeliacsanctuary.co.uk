<?php

declare(strict_types=1);

use App\Models\EatingOut\EateryRecommendation;
use Illuminate\Support\Facades\Route;

Route::get('/ai-status/{recommendation}', function (EateryRecommendation $recommendation) {
    $aiData = $recommendation->aiData;

    return response()->json([
        'status' => match (true) {
            $aiData === null => 'none',
            $aiData->failed_at !== null => 'failed',
            $aiData->completed_at !== null => 'completed',
            default => 'pending',
        },
    ]);
})->whereNumber('recommendation');
