<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AskSealiacChat;
use App\Models\AskSealiacChatMessage;

class AskSealiacChatMessageFactory extends Factory
{
    protected $model = AskSealiacChatMessage::class;

    public function definition(): array
    {
        return [
            'ask_sealiac_chat_id' => Factory::factoryForModel(AskSealiacChat::class),
            'prompt' => $this->faker->sentence(),
            'response' => $this->faker->paragraph(),
            'tool_uses' => [],
        ];
    }
}
