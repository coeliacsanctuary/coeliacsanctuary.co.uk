<?php

declare(strict_types=1);

namespace Jpeters8889\RefreshAdsTxt;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class RefreshAdsTxt extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     */
    public function boot(): void
    {
        Nova::mix('refresh-ads-txt', __DIR__ . '/../dist/mix-manifest.json');
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     */
    public function menu(Request $request): MenuSection
    {
        return MenuSection::make('Refresh Ads Txt')
            ->path('/refresh-ads-txt')
            ->icon('server');
    }
}
