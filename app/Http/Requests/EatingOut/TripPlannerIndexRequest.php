<?php

declare(strict_types=1);

namespace App\Http\Requests\EatingOut;

use App\DataObjects\EatingOut\TripDestination;
use Illuminate\Foundation\Http\FormRequest;

class TripPlannerIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }

    /** @return array{label: string, lat: float, lng: float} | null */
    public function destination(): ?array
    {
        if ( ! $this->filled('to')) {
            return null;
        }

        return TripDestination::fromQueryValue($this->string('to')->toString())?->toArray();
    }
}
