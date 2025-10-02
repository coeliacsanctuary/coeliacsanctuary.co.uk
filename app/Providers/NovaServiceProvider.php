<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Nova\Dashboards\Main;
use App\Nova\Dashboards\Shop;
use App\Nova\FieldRegistrar;
use App\Nova\Menu;
use App\Nova\NovaMacros;
use App\Nova\ResourceRegistrar;
use Illuminate\Support\Facades\Gate;
use Jpeters8889\AddressField\FieldServiceProvider as AddressFieldServiceProvider;
use Jpeters8889\OrderDispatchSlip\OrderDispatchSlip as DispatchSlipTool;
use Jpeters8889\ShopDailyStock\ShopDailyStock;
use Jpeters8889\WteNationwideBranchImport\WteNationwideBranchImport;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Nova::withBreadcrumbs();

        Nova::booted(function (): void {
            Menu::build();
        });
    }

    public function register(): void
    {
        parent::register();

        AddressFieldServiceProvider::setGoogleApiKey(config('services.google.maps.admin'));

        FieldRegistrar::handle();
        NovaMacros::register();
    }

    protected function resources(): void
    {
        ResourceRegistrar::handle();
    }

    protected function routes(): void
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->register();
    }

    protected function gate(): void
    {
        Gate::define('viewNova', fn (?User $user) => $user?->exists());
    }

    protected function dashboards()
    {
        return [
            Main::make(),
            Shop::make(),
        ];
    }

    public function tools(): array
    {
        return [
            new DispatchSlipTool(),
            new ShopDailyStock(),
            new WteNationwideBranchImport(),
        ];
    }
}
