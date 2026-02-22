<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Middleware;

use App\Ai\Middleware\LogChatMiddleware;
use App\Ai\State\ChatContext;
use App\Models\AskSealiacChat;
use Illuminate\Http\Request;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
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

        $prompt = Mockery::mock(AgentPrompt::class);
        (new ReflectionProperty(AgentPrompt::class, 'prompt'))->setValue($prompt, 'Hello');

        $response = new AgentResponse('inv-1', 'Hi there!', new Usage(), new Meta());

        $middleware = new LogChatMiddleware();
        $middleware->handle($prompt, fn () => $response);

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

        $prompt = Mockery::mock(AgentPrompt::class);
        (new ReflectionProperty(AgentPrompt::class, 'prompt'))->setValue($prompt, 'What is coeliac disease?');

        $response = new AgentResponse('inv-1', 'Coeliac disease is...', new Usage(), new Meta());

        $middleware = new LogChatMiddleware();
        $middleware->handle($prompt, fn () => $response);

        $this->assertCount(1, $chat->refresh()->messages);
        $this->assertEquals('What is coeliac disease?', $chat->messages->first()->prompt);
        $this->assertEquals('Coeliac disease is...', $chat->messages->first()->response);
    }

    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }
}
