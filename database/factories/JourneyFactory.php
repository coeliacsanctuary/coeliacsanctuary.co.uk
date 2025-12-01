<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Journeys\Journey;
use Carbon\Carbon;
use Illuminate\Support\Str;

class JourneyFactory extends Factory
{
    protected $model = Journey::class;

    public function definition()
    {
        return [
            'session_id' => Str::random(),
            'ended_at' => null,
        ];
    }

    public function forSession(string $id): self
    {
        return $this->state(['session_id' => $id]);
    }

    public function ended(?Carbon $endedAt = null): self
    {
        return $this->state(['ended_at' => $endedAt ?? now()]);
    }
}
