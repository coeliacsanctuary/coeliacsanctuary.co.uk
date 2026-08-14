<?php

declare(strict_types=1);

namespace Jpeters8889\EateryCollectionsQueryBuilder;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class EateryCollectionsQueryBuilder extends Tool
{
    public function boot(): void
    {
        Nova::mix('eatery-collections-query-builder', __DIR__ . '/../dist/mix-manifest.json');
    }

    public function menu(Request $request): MenuSection
    {
        return MenuSection::make('Eatery Query Builder')
            ->path('/eatery-collections-query-builder')
            ->icon('search');
    }
}
