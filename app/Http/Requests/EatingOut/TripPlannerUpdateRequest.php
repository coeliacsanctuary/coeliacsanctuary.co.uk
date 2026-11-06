<?php

declare(strict_types=1);

namespace App\Http\Requests\EatingOut;

use App\Concerns\EatingOut\ValidatesTripPlannerInput;
use App\Models\EatingOut\EateryTrip;
use Illuminate\Foundation\Http\FormRequest;

class TripPlannerUpdateRequest extends FormRequest
{
    use ValidatesTripPlannerInput;

    public function rules(): array
    {
        return $this->tripPlannerRules();
    }

    public function authorize(): bool
    {
        /** @var EateryTrip $trip */
        $trip = $this->route('eateryTrip');

        /** @var string | null $token */
        $token = $this->cookie("trip-planner-{$trip->key}");

        return $trip->canBeEditedWith($token);
    }
}
