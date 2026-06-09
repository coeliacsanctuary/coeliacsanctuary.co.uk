<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AskSealiacChat;

class AskSealiacChatFactory extends Factory
{
    protected $model = AskSealiacChat::class;

    public function definition(): array
    {
        return [
            'session_id' => $this->faker->uuid(),
            'chat_id' => $this->faker->uuid(),
            'summary' => null,
        ];
    }

    public function withSummary(?string $summary = null): static
    {
        return $this->state(fn () => [
            'summary' => $summary ?? $this->faker->sentence(),
        ]);
    }
}
