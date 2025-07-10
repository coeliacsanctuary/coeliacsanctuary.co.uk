<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ComputeRecommendAPlaceBackLinkAction
{
    public function handle(): array
    {
        $previous = URL::previous(route('eating-out.index'));

        /** @var \Illuminate\Routing\Route | null $route */
        $route = Route::getRoutes()->match(Request::create($previous));

        /** @phpstan-ignore-next-line  */
        $county = Str::headline($route?->parameter('county') ?? 'London');

        /** @phpstan-ignore-next-line  */
        $town = Str::headline($route?->parameter('town') ?? $route?->parameter('borough') ?? '');

        /** @phpstan-ignore-next-line  */
        $area = Str::headline($route?->parameter('area') ?? '');

        $name = match ($route?->getName()) {
            'eating-out.county', 'eating-out.london' => "Back to {$county}",
            'eating-out.browse', 'eating-out.browse.any' => 'Back to map',
            'eating-out.search.show', 'search.index' => 'Back to search results',
            'eating-out.town', 'eating-out.london.borough' => "Back to {$town}",
            'eating-out.london.borough.area' => "Back to {$area}",
            default => 'Back to Eating Out Guide'
        };

        if ($name === 'Back to Eating Out Guide') {
            $previous = route('eating-out.index');
        }

        return [$name, $previous];
    }
}
