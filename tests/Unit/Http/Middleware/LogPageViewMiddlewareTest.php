<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Actions\Journey\QueuePageViewAction;
use App\DataObjects\Journey\QueuedPageViewData;
use App\Http\Middleware\LogPageViewMiddleware;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogPageViewMiddlewareTest extends TestCase
{
    #[Test]
    public function itDoesNothingIfJourneyIsDisabled(): void
    {
        config()->set('coeliac.journey.enabled', false);

        $request = Request::create('/');

        $this->mock(QueuePageViewAction::class)->shouldNotReceive('handle');

        app(LogPageViewMiddleware::class)->handle($request, fn () => null);
    }

    #[Test]
    public function itDoesNothingIfTheRequestIsNotAGetRequest(): void
    {
        $request = Request::create('/', 'POST');

        $request->headers->set('X-Journey-Id', 'foo');

        $this->mock(QueuePageViewAction::class)->shouldNotReceive('handle');

        app(LogPageViewMiddleware::class)->handle($request, fn () => null);
    }

    #[Test]
    public function itDoesNothingIfTheUrlIsBlackListed(): void
    {
        config()->set('coeliac.journey.dont-track', ['some/url']);

        $request = Request::create('/some/url');

        $request->headers->set('X-Journey-Id', 'foo');

        $this->mock(QueuePageViewAction::class)->shouldNotReceive('handle');

        app(LogPageViewMiddleware::class)->handle($request, fn () => null);
    }

    #[Test]
    public function itDoesNothingIfTheRequestDoesntHaveTheJourneyIdHeader(): void
    {
        $request = Request::create('/');

        $this->mock(QueuePageViewAction::class)->shouldNotReceive('handle');

        app(LogPageViewMiddleware::class)->handle($request, fn () => null);
    }

    #[Test]
    public function itCallsTheQueuePageViewAction(): void
    {
        $request = Request::create('/some/path?foo=bar');

        $store = app(Store::class);
        $request->setLaravelSession($store);

        $request->headers->set('X-Journey-Id', 'foo');

        $this->mock(QueuePageViewAction::class)
            ->shouldReceive('handle')
            ->withArgs(function (string $journeyId, string $sessionId, string $url) use ($store) {
                $this->assertEquals('foo', $journeyId);
                $this->assertEquals($store->getId(), $sessionId);
                $this->assertEquals('some/path', $url);

                return true;
            })
            ->once()
            ->andReturn(new QueuedPageViewData(
                Str::uuid()->toString(),
                'foo',
                $store->getId(),
                '/some/path',
                time(),
            ));

        app(LogPageViewMiddleware::class)->handle($request, fn ($request) => $request);
    }
}
