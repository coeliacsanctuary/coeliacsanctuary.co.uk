<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\AskSealiac;

use App\Ai\Agents\AskSealiac;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Laravel\Ai\Prompts\AgentPrompt;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        AskSealiac::fake(['Hello! How can I help you?']);
    }

    #[Test]
    public function itReturnsAnErrorWithoutAChatId(): void
    {
        $this->makeRequest(['chatId' => null])
            ->assertJsonValidationErrorFor('chatId');
    }

    #[Test]
    public function itReturnsAnErrorWhenChatIdIsNotAString(): void
    {
        $this->makeRequest(['chatId' => 123])
            ->assertJsonValidationErrorFor('chatId');
    }

    #[Test]
    public function itReturnsAnErrorWhenChatIdIsTooShort(): void
    {
        $this->makeRequest(['chatId' => 'abc'])
            ->assertJsonValidationErrorFor('chatId');
    }

    #[Test]
    public function itReturnsAnErrorWhenChatIdIsTooLong(): void
    {
        $this->makeRequest(['chatId' => 'abcdefghi'])
            ->assertJsonValidationErrorFor('chatId');
    }

    #[Test]
    public function itReturnsAnErrorWithoutAPrompt(): void
    {
        $this->makeRequest(['prompt' => null])
            ->assertJsonValidationErrorFor('prompt');
    }

    #[Test]
    public function itReturnsAnErrorWhenPromptIsNotAString(): void
    {
        $this->makeRequest(['prompt' => 123])
            ->assertJsonValidationErrorFor('prompt');
    }

    #[Test]
    public function itReturnsAnErrorWhenPromptIsTooShort(): void
    {
        $this->makeRequest(['prompt' => 'ab'])
            ->assertJsonValidationErrorFor('prompt');
    }

    #[Test]
    public function itReturnsAnErrorWhenPromptIsTooLong(): void
    {
        $this->makeRequest(['prompt' => Str::random(501)])
            ->assertJsonValidationErrorFor('prompt');
    }

    #[Test]
    public function itReturnsAnErrorWhenMessagesIsNotAnArray(): void
    {
        $this->makeRequest(['messages' => 'not-an-array'])
            ->assertJsonValidationErrorFor('messages');
    }

    #[Test]
    public function itReturnsAnErrorWhenMessagesExceedsMaxCount(): void
    {
        $messages = array_fill(0, 51, ['role' => 'user', 'message' => 'hello']);

        $this->makeRequest(['messages' => $messages])
            ->assertJsonValidationErrorFor('messages');
    }

    #[Test]
    public function itReturnsAnErrorWhenMessageRoleIsMissing(): void
    {
        $this->makeRequest(['messages' => [['message' => 'hello']]])
            ->assertJsonValidationErrorFor('messages.0.role');
    }

    #[Test]
    public function itReturnsAnErrorWhenMessageRoleIsInvalid(): void
    {
        $this->makeRequest(['messages' => [['role' => 'system', 'message' => 'hello']]])
            ->assertJsonValidationErrorFor('messages.0.role');
    }

    #[Test]
    public function itReturnsAnErrorWhenMessageContentIsMissing(): void
    {
        $this->makeRequest(['messages' => [['role' => 'user']]])
            ->assertJsonValidationErrorFor('messages.0.message');
    }

    #[Test]
    public function itReturnsASuccessfulStreamedResponse(): void
    {
        $this->makeRequest()
            ->assertStreamedContent('Hello! How can I help you?')
            ->assertSuccessful();
    }

    #[Test]
    public function itPromptsTheAskSealiacAgent(): void
    {
        $this->makeRequest(['prompt' => 'What is coeliac disease?']);

        AskSealiac::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('What is coeliac disease?'));
    }

    #[Test]
    public function itPassesMessagesToTheAgent(): void
    {
        $messages = [
            ['role' => 'user', 'message' => 'Hello'],
            ['role' => 'assistant', 'message' => 'Hi there!'],
        ];

        $this->makeRequest(['messages' => $messages]);

        AskSealiac::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('Tell me about gluten free food'));
    }

    #[Test]
    public function itAcceptsAnEmptyMessagesArray(): void
    {
        $this->makeRequest(['messages' => []])->assertSuccessful();
    }

    protected function makeRequest(array $data = []): TestResponse
    {
        return $this->postJson(route('api.ask-sealiac'), [
            'chatId' => 'abcdefgh',
            'prompt' => 'Tell me about gluten free food',
            'messages' => [],
            ...$data,
        ]);
    }
}
