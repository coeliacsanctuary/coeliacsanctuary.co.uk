<?php

declare(strict_types=1);

namespace App\Http\Requests\EatingOut;

use App\Concerns\EatingOut\ValidatesTripPlannerInput;
use Illuminate\Foundation\Http\FormRequest;

class TripPlannerCreateRequest extends FormRequest
{
    use ValidatesTripPlannerInput;

    public function rules(): array
    {
        return $this->tripPlannerRules();
    }
}
