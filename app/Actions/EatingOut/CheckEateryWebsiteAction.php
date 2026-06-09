<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\DataObjects\EatingOut\EateryWebsiteCheckResult;
use App\Models\EatingOut\Eatery;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CheckEateryWebsiteAction
{
    protected array $retryableStatusCodes = [
        SymfonyResponse::HTTP_UNAUTHORIZED,
        SymfonyResponse::HTTP_FORBIDDEN,
        SymfonyResponse::HTTP_METHOD_NOT_ALLOWED,
    ];

    public function handle(Eatery $eatery): EateryWebsiteCheckResult
    {
        if ( ! $eatery->website) {
            return new EateryWebsiteCheckResult(
                success: false,
                errorMessage: 'No website URL configured',
            );
        }

        try {
            $response = $this->sendHeadRequest($eatery);

            if ($response->successful()) {
                return $this->returnSuccess($response->status());
            }

            if ($this->shouldRetryFailedRequest($response->status())) {
                $response = $this->sendGetRequest($eatery);

                if ($response->successful()) {
                    return $this->returnSuccess($response->status());
                }
            }

            return new EateryWebsiteCheckResult(
                success: false,
                statusCode: $response->status(),
                errorMessage: "HTTP {$response->status()} response",
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

    protected function returnSuccess(int $status): EateryWebsiteCheckResult
    {
        return new EateryWebsiteCheckResult(
            success: true,
            statusCode: $status,
        );
    }

    protected function sendGetRequest(Eatery $eatery): Response
    {
        return $this->sendRequest($eatery->website, 'GET');
    }

    protected function sendHeadRequest(Eatery $eatery): Response
    {
        return $this->sendRequest($eatery->website);
    }

    /** @throws ConnectionException */
    protected function sendRequest(string $url, string $method = 'HEAD'): Response
    {
        return Http::timeout(10)
            ->withOptions(['allow_redirects' => true])
            ->$method($url);
    }

    protected function shouldRetryFailedRequest(int $status): bool
    {
        return in_array($status, $this->retryableStatusCodes);
    }
}
