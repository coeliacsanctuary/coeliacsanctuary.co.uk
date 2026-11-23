<?php

declare(strict_types=1);

namespace Jpeters8889\WteNationwideBranchImport;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class WteNationwideBranchImport extends Tool
{
    public function boot(): void
    {
        Nova::mix('wte-nationwide-branch-import', __DIR__ . '/../dist/mix-manifest.json');
    }

    public function menu(Request $request): MenuSection
    {
        return MenuSection::make('Wte Nationwide Branch Import')
            ->path('/wte-nationwide-branch-import')
            ->icon('server');
    }
}
