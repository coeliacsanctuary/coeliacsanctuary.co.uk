<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetCountryDescriptionAction;
use App\Ai\Agents\EateryCountryDescriptionAgent;
use App\Models\EatingOut\EateryCountry;
use Closure;
use Illuminate\Support\Facades\Cache;
use Laravel\Ai\QueuedAgentPrompt;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\QueuedAgentResponse;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetCountryDescriptionActionTest extends TestCase
{
    protected EateryCountry $country;

    protected function setUp(): void
    {
        parent::setUp();

        $this->country = $this->create(EateryCountry::class);
    }

    #[Test]
    public function itReturnsTheExistingDescriptionAsInlineMarkdownIfOneExists(): void
    {
        $this->country->update(['description' => '**foo** bar']);

        $result = app(GetCountryDescriptionAction::class)->handle($this->country);

        $this->assertEquals('<strong>foo</strong> bar' . "\n", $result);
    }

    #[Test]
    public function itDoesNotQueueTheAgentWhenADescriptionAlreadyExists(): void
    {
        EateryCountryDescriptionAgent::fake();

        $this->country->update(['description' => 'existing description']);

        app(GetCountryDescriptionAction::class)->handle($this->country);

        EateryCountryDescriptionAgent::assertNeverQueued();
    }

    #[Test]
    public function itQueuesTheAgentWhenNoDescriptionExists(): void
    {
        EateryCountryDescriptionAgent::fake();

        app(GetCountryDescriptionAction::class)->handle($this->country);

        EateryCountryDescriptionAgent::assertQueued(fn (QueuedAgentPrompt $prompt) => true);
    }

    #[Test]
    public function theQueuedPromptContainsTheCountryName(): void
    {
        EateryCountryDescriptionAgent::fake();

        app(GetCountryDescriptionAction::class)->handle($this->country);

        EateryCountryDescriptionAgent::assertQueued(
            fn (QueuedAgentPrompt $prompt) => str_contains($prompt->prompt, $this->country->country)
        );
    }

    #[Test]
    public function itReturnsADefaultDescriptionWhileWaitingForTheAgent(): void
    {
        EateryCountryDescriptionAgent::fake();

        $result = app(GetCountryDescriptionAction::class)->handle($this->country);

        $this->assertNotEmpty($result);
        $this->assertStringContainsString($this->country->country, $result);
    }

    #[Test]
    public function theAgentCallbackUpdatesTheCountryDescription(): void
    {
        $capturedCallback = null;

        $mockQueuedResponse = Mockery::mock(QueuedAgentResponse::class);
        $mockQueuedResponse->shouldReceive('then')
            ->once()
            ->andReturnUsing(function (Closure $callback) use (&$capturedCallback, $mockQueuedResponse) {
                $capturedCallback = $callback;

                return $mockQueuedResponse;
            });

        $this->mock(EateryCountryDescriptionAgent::class)
            ->shouldReceive('queue')
            ->andReturn($mockQueuedResponse);

        app(GetCountryDescriptionAction::class)->handle($this->country);

        $mockResponse = Mockery::mock(AgentResponse::class);
        $mockResponse->shouldReceive('__toString')->andReturn('AI generated description');

        $capturedCallback($mockResponse);

        $this->country->refresh();
        $this->assertEquals('AI generated description', $this->country->description);
    }

    #[Test]
    public function theAgentCallbackForgetsTheCache(): void
    {
        $capturedCallback = null;

        $mockQueuedResponse = Mockery::mock(QueuedAgentResponse::class);
        $mockQueuedResponse->shouldReceive('then')
            ->once()
            ->andReturnUsing(function (Closure $callback) use (&$capturedCallback, $mockQueuedResponse) {
                $capturedCallback = $callback;

                return $mockQueuedResponse;
            });

        $this->mock(EateryCountryDescriptionAgent::class)
            ->shouldReceive('queue')
            ->andReturn($mockQueuedResponse);

        app(GetCountryDescriptionAction::class)->handle($this->country);

        Cache::partialMock()
            ->shouldReceive('forget')
            ->once()
            ->with(config('coeliac.cacheable.eating-out.index-counts'));

        $mockResponse = Mockery::mock(AgentResponse::class);
        $mockResponse->shouldReceive('__toString')->andReturn('AI generated description');

        $capturedCallback($mockResponse);
    }
}
