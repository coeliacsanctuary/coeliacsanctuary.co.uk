<?php

declare(strict_types=1);

namespace App\Support\MagicRouting\Resolvers;

use App\Actions\EatingOut\MagicRouting\FindEateryMagicRouteRecordAction;
use App\Contracts\RouteFallbackResolverContract;
use App\Enums\EatingOut\EateryMagicRouteType;
use App\Http\Controllers\ResolvedFallbacks\HundredPercentGlutenFreeEateries\CountyController;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Join;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use App\Services\EatingOut\Collection\Configuration;
use App\Support\MagicRouting\RouteToController;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HundredPercentGlutenFreeEateriesFallbackResolver implements RouteFallbackResolverContract
{
    protected string $regex = '/^eating-100-percent-gluten-free-in-([a-z-]+)$/';

    public function canHandle(Request $request): bool
    {
        return Str::isMatch($this->regex, $request->path());
    }

    public function handle(Request $request): Responsable|RedirectResponse
    {
        $location = Str::match($this->regex, $request->path());

        $routeRecord = app(FindEateryMagicRouteRecordAction::class)->handle(
            EateryMagicRouteType::HundredPercentGlutenFree,
            $location,
            $this->buildConfiguration(...),
        );

        // return inertia page with the resolved eateries
        // if area, then return the page with list
        // if town, and not london, return the page with the list
        // if town, and london, return a page spit into sections via areas
        // if county, return a page split into sections via towns

        $controller = match ($routeRecord->location::class) {
            EateryCounty::class => CountyController::class,
            EateryTown::class => 'EatingOut/MagicRoutes/HundredPercentGlutenFree/Town',
        };

        return app(RouteToController::class)->handle(app($controller), [
            'routeRecord' => $routeRecord,
        ]);
    }

    protected function buildConfiguration(Configuration $configuration): Configuration
    {
        return $configuration
            ->addJoin(new Join('wheretoeat_assigned_features', 'wheretoeat_assigned_features.wheretoeat_id', 'wheretoeat.id'))
            ->addWhere(new Where('wheretoeat_assigned_features.feature_id', '=', 1)); // 100 percent gluten free
    }
}
