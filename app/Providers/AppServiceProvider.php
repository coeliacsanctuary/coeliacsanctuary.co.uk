<?php

declare(strict_types=1);

namespace App\Providers;

use App\Infrastructure\MailChannel;
use App\Search\Eateries;
use App\Services\GoogleMerchant\GoogleMerchantClient;
use App\Services\GoogleMerchant\GoogleMerchantProductManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\Channels\MailChannel as IlluminateMailChannel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Nightwatch\Facades\Nightwatch;
use Laravel\Nightwatch\Records\CacheEvent;
use Laravel\Nightwatch\Records\OutgoingRequest;
use Spatie\Mjml\Mjml;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Mjml::class, fn () => Mjml::new());
        $this->app->register(MacroServiceProvider::class);
        $this->app->alias(MailChannel::class, IlluminateMailChannel::class);

        $this->app->singleton(GoogleMerchantProductManager::class);

        $this->app->singleton(GoogleMerchantClient::class, fn () => new GoogleMerchantClient(
            enabled: config()->boolean('google-merchant.enabled', false),
            merchantId: config()->string('google-merchant.merchant_id', ''),
            serviceAccountKeyPath: config()->string('google-merchant.service_account_key_path', ''),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        Model::shouldBeStrict( ! $this->app->runningInConsole());

        JsonResource::withoutWrapping();

        Eateries::bootSearchable();

        Vite::prefetch(concurrency: 3);

        Blade::directive('preloadImage', fn () => "<?php echo app(App\Actions\PreloadHeaderImageAction::class)->handle(); ?>");

        Nightwatch::rejectCacheEvents(fn (CacheEvent $event) => ! str_contains($event->key, '.'));

        Nightwatch::rejectOutgoingRequests(fn (OutgoingRequest $request) => $request->url === '127.0.0.1');

        RateLimiter::for('metrics', fn () => Limit::perSecond(5));
    }
}
