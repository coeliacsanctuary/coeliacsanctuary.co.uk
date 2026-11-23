<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AskSealiacChat;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AskSealiacChat>
 */
class AskSealiacChatFactory extends Factory
{
    protected $model = AskSealiacChat::class;

    public function definition(): array
    {
        return [
            'session_id' => Str::random(40),
            'chat_id' => Str::of($this->faker->uuid())->replace('-', '')->substr(0, 8)->toString(),
            'summary' => null,
        ];
    }

    public function withSummary(?string $summary = null): static
    {
        return $this->state(fn (): array => [
            'summary' => $summary ?? $this->faker->sentence(),
        ]);
    }
}
