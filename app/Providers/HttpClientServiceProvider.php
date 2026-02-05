<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class HttpClientServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerGetAddress();
        $this->registerIdealPostcodes();
    }

    protected function registerIdealPostcodes(): void
    {
        /** @var string $url */
        $url = config('services.idealPostcodes.url');

        /** @var string $apiKey */
        $apiKey = config('services.idealPostcodes.key');

        Http::macro('idealPostcodes', fn () => Http::baseUrl($url)->withHeader('Authorization', "IDEALPOSTCODES api_key=\"{$apiKey}\""));
    }

    protected function registerGetAddress(): void
    {
        /** @var string $url */
        $url = config('services.getAddress.url');

        /** @var string $apiKey */
        $apiKey = config('services.getAddress.key');

        Http::macro('getAddress', fn () => Http::baseUrl($url)->withBasicAuth('http', $apiKey));
    }
}
