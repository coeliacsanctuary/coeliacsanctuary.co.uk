<?php

declare(strict_types=1);

namespace App\Actions\OpenGraphImages;

use App\Contracts\OpenGraphActionContract;
use App\Enums\EatingOut\EateryType;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\View\View;

class GenerateAreaOpenGraphImageAction implements OpenGraphActionContract
{
    public function handle(Eatery|NationwideBranch|EateryArea|EateryTown|EateryCounty|EateryCountry $model): View
    {
        /** @var EateryArea $model */
        $model->loadMissing(['media', 'town', 'town.county', 'town.county.media', 'town.county.country']);

        $eateries = $model->liveEateries()->where('type_id', EateryType::EATERY)->count() + $model->liveBranches()->count();
        $attractions = $model->liveEateries()->where('type_id', EateryType::ATTRACTION)->count();
        $hotels = $model->liveEateries()->where('type_id', EateryType::HOTEL)->count();
        $reviews = $model->reviews()->count();

        $width = max(collect([$eateries, $attractions, $hotels, $reviews])->filter()->count(), 2);

        return view('og-images.eating-out.area', [
            'area' => $model,
            'eateries' => $eateries,
            'attractions' => $attractions,
            'hotels' => $hotels,
            'reviews' => $reviews,
            'width' => $width,
        ]);
    }
}
