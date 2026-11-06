<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class HttpClientServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerIdealPostcodes();
        $this->registerFinanceService();
        $this->registerOpenRouteService();
    }

    protected function registerOpenRouteService(): void
    {
        Http::macro(
            'openRouteService',
            fn () => Http::baseUrl(config()->string('services.openRouteService.url'))
                ->withHeader('Authorization', config()->string('services.openRouteService.key'))
        );
    }

    protected function registerFinanceService(): void
    {
        Http::macro(
            'financeService',
            fn () => Http::baseUrl(config()->string('services.finance.url'))
                ->withToken(config()->string('services.finance.key'))
                ->throw()
        );
    }

    protected function registerIdealPostcodes(): void
    {
        /** @var string $url */
        $url = config('services.idealPostcodes.url');

        /** @var string $apiKey */
        $apiKey = config('services.idealPostcodes.key');

        Http::macro('idealPostcodes', fn () => Http::baseUrl($url)->withHeader('Authorization', "IDEALPOSTCODES api_key=\"{$apiKey}\""));
    }

}
