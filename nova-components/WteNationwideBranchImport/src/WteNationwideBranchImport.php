<?php

declare(strict_types=1);

namespace Jpeters8889\WteNationwideBranchImport;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class WteNationwideBranchImport extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     */
    public function boot(): void
    {
        Nova::mix('wte-nationwide-branch-import', __DIR__ . '/../dist/mix-manifest.json');
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     */
    public function menu(Request $request): MenuSection
    {
        return MenuSection::make('Wte Nationwide Branch Import')
            ->path('/wte-nationwide-branch-import')
            ->icon('server');
    }
}
