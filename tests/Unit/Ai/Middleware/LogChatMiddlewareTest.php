<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Middleware;

use App\Ai\Middleware\LogChatMiddleware;
use App\Ai\State\ChatContext;
use App\Models\AskSealiacChat;
use Illuminate\Http\Request;
use Laravel\Ai\Gateway\StepResponse;
use Laravel\Ai\Gateway\StepResult;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\PendingStep;
use Laravel\Ai\Responses\Data\FinishReason;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\TextUsage;
use Laravel\Ai\Responses\Data\ToolCall;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogChatMiddlewareTest extends TestCase
{
    protected Request $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = Request::create('/');
        $this->request->setLaravelSession(app('session.store'));

        app()->instance('request', $this->request);
    }

    #[Test]
    public function itNullsTheSummaryWhenANewMessageIsLogged(): void
    {
        $sessionId = $this->request->session()->getId();

        $chat = $this->build(AskSealiacChat::class)->withSummary()->create([
            'session_id' => $sessionId,
            'chat_id' => 'test-chat',
        ]);

        $this->assertNotNull($chat->summary);

        ChatContext::setChatId('test-chat');

        $this->runStep($this->pendingStep('Hello'), $this->stepResponse('Hi there!'));

        $this->assertNull($chat->refresh()->summary);
    }

    #[Test]
    public function itCreatesAMessageForTheChat(): void
    {
        $sessionId = $this->request->session()->getId();

        $chat = $this->create(AskSealiacChat::class, [
            'session_id' => $sessionId,
            'chat_id' => 'test-chat',
        ]);

        ChatContext::setChatId('test-chat');
        ChatContext::addToolUse('search_recipes', ['term' => 'bread']);

        $this->runStep($this->pendingStep('What is coeliac disease?'), $this->stepResponse('Coeliac disease is...'));

        $this->assertCount(1, $chat->refresh()->messages);
        $this->assertEquals('What is coeliac disease?', $chat->messages->first()->prompt);
        $this->assertEquals('Coeliac disease is...', $chat->messages->first()->response);
        $this->assertEquals([['tool' => 'search_recipes', 'data' => ['term' => 'bread']]], $chat->messages->first()->tool_uses);
    }

    #[Test]
    public function itDoesNotLogAStepThatCallsTools(): void
    {
        $sessionId = $this->request->session()->getId();

        $chat = $this->create(AskSealiacChat::class, [
            'session_id' => $sessionId,
            'chat_id' => 'test-chat',
        ]);

        ChatContext::setChatId('test-chat');
        ChatContext::addToolUse('search_recipes', ['term' => 'bread']);

        $this->runStep($this->pendingStep('Find me a bread recipe'), $this->stepResponse('', [new ToolCall('call_1', 'search_recipes', [])]));

        $this->assertCount(0, $chat->refresh()->messages);
        $this->assertCount(1, ChatContext::getToolUses());
    }

    #[Test]
    public function itLogsTheLastUserMessageAsThePrompt(): void
    {
        $sessionId = $this->request->session()->getId();

        $chat = $this->create(AskSealiacChat::class, [
            'session_id' => $sessionId,
            'chat_id' => 'test-chat',
        ]);

        ChatContext::setChatId('test-chat');

        $step = $this->pendingStep('And a cake recipe?', [
            new UserMessage('Find me a bread recipe'),
            new AssistantMessage('Here is a bread recipe...'),
        ]);

        $this->runStep($step, $this->stepResponse('Here is a cake recipe...'));

        $this->assertEquals('And a cake recipe?', $chat->refresh()->messages->first()->prompt);
    }

    #[Test]
    public function itLogsWhenTheStepLimitIsReached(): void
    {
        $sessionId = $this->request->session()->getId();

        $chat = $this->create(AskSealiacChat::class, [
            'session_id' => $sessionId,
            'chat_id' => 'test-chat',
        ]);

        ChatContext::setChatId('test-chat');

        $this->runStep(
            $this->pendingStep('Find me a bread recipe', isFinalStep: true),
            $this->stepResponse('I could not finish that search', [new ToolCall('call_1', 'search_recipes', [])]),
        );

        $this->assertCount(1, $chat->refresh()->messages);
        $this->assertEquals('I could not finish that search', $chat->messages->first()->response);
    }

    protected function runStep(PendingStep $step, StepResponse $response): void
    {
        new LogChatMiddleware()
            ->handle($step, fn (PendingStep $step): StepResult => new StepResult($response, $step))
            ->response();
    }

    protected function pendingStep(string $prompt, array $history = [], bool $isFinalStep = false): PendingStep
    {
        return new PendingStep(
            number: 0,
            isFinalStep: $isFinalStep,
            provider: 'openai',
            model: 'gpt-5.2',
            instructions: null,
            messages: [...$history, new UserMessage($prompt)],
            tools: [],
            schema: null,
            options: null,
        );
    }

    /** @param ToolCall[] $toolCalls */
    protected function stepResponse(string $text, array $toolCalls = []): StepResponse
    {
        return new StepResponse(
            $text,
            $toolCalls,
            $toolCalls === [] ? FinishReason::Stop : FinishReason::ToolCalls,
            new TextUsage(),
            new Meta(),
        );
    }

    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }
}
