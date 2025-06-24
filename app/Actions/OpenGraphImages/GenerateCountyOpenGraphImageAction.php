<?php

declare(strict_types=1);

namespace App\Actions\OpenGraphImages;

use App\Contracts\OpenGraphActionContract;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\View\View;

class GenerateCountyOpenGraphImageAction implements OpenGraphActionContract
{
    public function handle(Eatery|NationwideBranch|EateryArea|EateryTown|EateryCounty|EateryCountry $model): View
    {
        /** @var EateryCounty $model */
        $model->loadMissing(['media', 'country']);

        $towns = $model->activeTowns()->count();
        $eateries = $model->eateries()->count() + $model->nationwideBranches()->count();
        $reviews = $model->reviews()->count();

        return view('og-images.eating-out.county', [
            'county' => $model,
            'towns' => $towns,
            'eateries' => $eateries,
            'reviews' => $reviews,
        ]);
    }
}
