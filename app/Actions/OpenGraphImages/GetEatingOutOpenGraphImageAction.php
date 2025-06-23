<?php

declare(strict_types=1);

namespace App\Actions\OpenGraphImages;

use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;

class GetEatingOutOpenGraphImageAction
{
    public function handle(Eatery|NationwideBranch|EateryArea|EateryTown|EateryCounty|EateryCountry $model): string
    {
        if ( ! $model->relationLoaded('openGraphImage')) {
            $model->load('openGraphImage');
        }

        if ($model->openGraphImage?->image_url) {
            return $model->openGraphImage->image_url;
        }

        CreateEatingOutOpenGraphImageJob::dispatch($model);

        return '';
    }
}
