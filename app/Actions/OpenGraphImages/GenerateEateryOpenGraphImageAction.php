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
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\View\View;

class GenerateEateryOpenGraphImageAction implements OpenGraphActionContract
{
    public function handle(Eatery|NationwideBranch|EateryArea|EateryTown|EateryCounty|EateryCountry $model): View
    {
        /** @var Eatery $model */
        $model->loadMissing(['town', 'county', 'country', 'reviews', 'reviewImages' => fn (Relation $builder) => $builder->getQuery()->latest()])
            ->loadCount(['nationwideBranches']);

        return view('og-images.eating-out.eatery', [
            'eatery' => $model,
        ]);
    }
}
