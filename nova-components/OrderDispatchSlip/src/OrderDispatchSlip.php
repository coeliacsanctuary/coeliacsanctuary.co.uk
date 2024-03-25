<?php

declare(strict_types=1);

namespace Jpeters8889\OrderDispatchSlip;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class OrderDispatchSlip extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     */
    public function boot(): void
    {
        Nova::script('order-dispatch-slip', __DIR__ . '/../dist/js/tool.js');
        Nova::style('order-dispatch-slip', __DIR__ . '/../dist/css/tool.css');
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @return mixed
     */
    public function menu(Request $request)
    {
        return MenuSection::make('Order Dispatch Slip')
            ->path('/order-dispatch-slip')
            ->icon('server');
    }
}
