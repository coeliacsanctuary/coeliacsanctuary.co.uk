<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Actions\Journey\ResolveJourneyAction;
use App\Http\Middleware\AttachJourneyMiddleware;
use App\Models\Journeys\Journey;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResolveJourneyMiddlewareTest extends TestCase
{
    #[Test]
    public function itDoesNothingIfJourneyIsDisabled(): void
    {
        config()->set('coeliac.journey.enabled', false);

        $request = Request::create('/');

        $this->mock(ResolveJourneyAction::class)->shouldNotReceive('handle');

        app(AttachJourneyMiddleware::class)->handle($request, fn () => null);
    }

    #[Test]
    public function ifAJourneyHeaderIsAlreadyInTheResponseItDoesNothing(): void
    {
        $request = Request::create('/');
        $request->headers->set('X-Journey-Id', 'foo');

        $this->mock(ResolveJourneyAction::class)->shouldNotReceive('handle');

        app(AttachJourneyMiddleware::class)->handle($request, fn ($request) => $request);
    }

    #[Test]
    public function itCallsTheResolveJourneyAction(): void
    {
        $request = Request::create('/');
        $store = app(Store::class);

        $request->setLaravelSession($store);

        $this->mock(ResolveJourneyAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($sessionId) use ($store) {
                $this->assertEquals($store->getId(), $sessionId);

                return true;
            })
            ->once()
            ->andReturn($this->create(Journey::class));

        app(AttachJourneyMiddleware::class)->handle($request, fn ($request) => $request);
    }

    #[Test]
    public function itAddsTheJourneyIdToTheResponse(): void
    {
        $request = Request::create('/');
        $store = app(Store::class);

        $request->setLaravelSession($store);

        $journey = $this->create(Journey::class);

        $this->mock(ResolveJourneyAction::class)
            ->shouldReceive('handle')
            ->andReturn($journey);

        $response = app(AttachJourneyMiddleware::class)->handle($request, fn ($request) => $request);

        $this->assertEquals($response->header('X-Journey-Id'), $journey->id);
    }
}
