<?php

declare(strict_types=1);

namespace Jpeters8889\ShopDailyStock;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class ShopDailyStock extends Tool
{
    public function boot(): void
    {
        Nova::script('shop-daily-stock', __DIR__ . '/../dist/js/tool.js');
        Nova::style('shop-daily-stock', __DIR__ . '/../dist/css/tool.css');
    }

    public function menu(Request $request)
    {
        return MenuSection::make('Shop Daily Stock')
            ->path('/shop-daily-stock')
            ->icon('server');
    }
}
