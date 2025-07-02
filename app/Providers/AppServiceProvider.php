<?php

declare(strict_types=1);

namespace App\Providers;

use App\Infrastructure\MailChannel;
use App\Search\Eateries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\Channels\MailChannel as IlluminateMailChannel;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
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
    }
}
