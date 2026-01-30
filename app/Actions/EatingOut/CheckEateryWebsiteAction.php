<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\DataObjects\EatingOut\EateryWebsiteCheckResult;
use App\Models\EatingOut\Eatery;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class CheckEateryWebsiteAction
{
    public function handle(Eatery $eatery): EateryWebsiteCheckResult
    {
        if ( ! $eatery->website) {
            return new EateryWebsiteCheckResult(
                success: false,
                errorMessage: 'No website URL configured',
            );
        }

        try {
            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->head($eatery->website);

            if ($response->failed()) {
                return new EateryWebsiteCheckResult(
                    success: false,
                    statusCode: $response->status(),
                    errorMessage: "HTTP {$response->status()} response",
                );
            }

            return new EateryWebsiteCheckResult(
                success: true,
                statusCode: $response->status(),
            );
        } catch (ConnectionException $e) {
            return new EateryWebsiteCheckResult(
                success: false,
                statusCode: 500,
                errorMessage: "Connection failed: {$e->getMessage()}",
                timedOut: true,
            );
        }
    }
}
