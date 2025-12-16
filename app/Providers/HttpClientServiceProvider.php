<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class HttpClientServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var string $url */
        $url = config('services.getAddress.url');

        /** @var string $apiKey */
        $apiKey = config('services.getAddress.key');

        Http::macro('getAddress', fn () => Http::baseUrl($url)->withBasicAuth('http', $apiKey));
    }
}
