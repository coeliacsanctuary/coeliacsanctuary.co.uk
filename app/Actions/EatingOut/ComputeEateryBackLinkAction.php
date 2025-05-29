<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class ComputeEateryBackLinkAction
{
    public function handle(Eatery $eatery): array
    {
        $previous = URL::previous(route('eating-out.town', ['county' => $eatery->branch->county ?? $eatery->county, 'town' => $eatery->branch->town ?? $eatery->town]));

        /** @var \Illuminate\Routing\Route | null $route */
        $route = Route::getRoutes()->match(Request::create($previous));

        /** @var EateryCounty $county */
        $county = $eatery->branch->county ?? $eatery->county;

        /** @var EateryTown $town */
        $town = $eatery->branch->town ?? $eatery->town;

        $name = match ($route?->getName()) {
            'eating-out.county' => "Back to {$county->county}",
            'eating-out.browse', 'eating-out.browse.any' => 'Back to map',
            'eating-out.search.show', 'search.index' => 'Back to search results',
            'eating-out.index' => 'Back to eating out guide',
            default => "Back to {$town->town}"
        };

        if ($name === "Back to {$town->town}") {
            $previous = $town->absoluteLink();
        }

        return [$name, $previous];
    }
}
