<?php

declare(strict_types=1);

namespace Jpeters8889\AddressField;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Spatie\Geocoder\Geocoder;

class FieldServiceProvider extends ServiceProvider
{
    protected static $googleApiKey;

    public function boot(): void
    {
        $this->app->booted(function (): void {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event): void {
            Nova::script('google', 'https://maps.google.com/maps/api/js?key=' . self::$googleApiKey);
            Nova::script('address-field', __DIR__ . '/../dist/js/field.js');
            Nova::style('address-field', __DIR__ . '/../dist/css/field.css');
        });
    }

    public static function setGoogleApiKey($apiKey): void
    {
        self::$googleApiKey = $apiKey;
    }

    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('/nova-vendor/jpeters8889/address-field')
            ->group(function (): void {
                Route::post('lookup', function (Request $request, Geocoder $geocoder) {
                    $geocoder->setApiKey(self::$googleApiKey);

                    $results = $geocoder->getAllCoordinatesForAddress($request->string('address')->toString());

                    return collect($results)
                        ->filter(fn (array $result) => ! in_array('natural_feature', $result['types']))
                        ->first();
                });
            });
    }
}
