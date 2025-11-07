<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\CheckRecommendedPlace\Steps;

use App\DataObjects\EatingOut\RecommendAPlaceExistsCheckData;
use App\Models\EatingOut\Eatery;
use Illuminate\Support\Str;

class CheckIfLoungeBranch extends AbstractStepAction
{
    protected function check(RecommendAPlaceExistsCheckData $data): void
    {
        if ( ! $data->name) {
            return;
        }

        $names = [
            'the lounge',
            'the lounges',
            'o lounge',
        ];

        if (Str::of($data->name)->lower()->contains($names)) {
            $eatery = Eatery::query()->where('name', 'The Lounges')->firstOrFail();

            if ( ! Str::of($data->name)->lower()->contains('o lounge')) {
                $data->found = true;
                $data->reason = 'It looks like you\'re recommending The Lounges, did you know we\'ve already got those listed on our Nationwide page?';
                $data->url = route('eating-out.nationwide.show', ['eatery' => $eatery->slug]);
                $data->label = 'Check out The Lounges';
            }

            $branch = $eatery->nationwideBranches()->where('name', 'like', "%{$data->name}%")->first();

            if ($branch) {
                $branch->load(['country', 'county', 'town']);

                $data->found = true;
                $data->reason = "It looks like you're recommending {$branch->name}, {$branch->full_location}, part of The Lounge's chain, but we've already got them listed!";
                $data->url = $branch->link();
                $data->label = "Check out {$branch->name}";
                $data->id = $eatery->id;
                $data->branchId = $branch->id;
            }
        }
    }
}
