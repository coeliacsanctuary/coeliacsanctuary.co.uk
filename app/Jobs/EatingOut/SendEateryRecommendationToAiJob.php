<?php

declare(strict_types=1);

namespace App\Jobs\EatingOut;

use App\Actions\EatingOut\SendEateryRecommendationToAiAction;
use App\Models\EatingOut\EateryRecommendation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

#[Tries(1)]
#[Timeout(300)]
class SendEateryRecommendationToAiJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected EateryRecommendation $eateryRecommendation)
    {
        //
    }

    public function handle(): void
    {
        if ( ! $this->checkIfEligible()) {
            return;
        }

        $aiInfo = app(SendEateryRecommendationToAiAction::class)->handle($this->eateryRecommendation);

        $this->eateryRecommendation->aiData()->create([
            'place_name' => $aiInfo->placeName,
            'place_address' => $aiInfo->placeAddress,
            'place_country' => $aiInfo->placeCountry,
            'place_county' => $aiInfo->placeCounty,
            'place_town' => $aiInfo->placeTown,
            'place_area' => $aiInfo->placeArea,
            'latitude' => $aiInfo->latitude,
            'longitude' => $aiInfo->longitude,
            'phone_number' => $aiInfo->phoneNumber,
            'website' => $aiInfo->website,
            'facebook' => $aiInfo->facebook,
            'instagram' => $aiInfo->instagram,
            'eatery_type' => $aiInfo->eateryType,
            'venue_type' => $aiInfo->venueType,
            'cuisine' => $aiInfo->cuisine,
            'info' => $aiInfo->info,
            'features' => $aiInfo->features,
            'explanation' => $aiInfo->explanation,
            'is_eligible' => $aiInfo->isEligible,
        ]);
    }

    protected function checkIfEligible(): bool
    {
        if ($this->eateryRecommendation->email === 'alisondwheatley@gmail.com') {
            return false;
        }

        return $this->eateryRecommendation->completed === false && $this->eateryRecommendation->ignored === false;
    }
}
