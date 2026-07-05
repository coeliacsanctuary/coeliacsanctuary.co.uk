<?php

declare(strict_types=1);

namespace Tests\Unit\Support\RouteFallbackResolvers;

use App\Actions\Redirects\CheckForRouteRedirectAction;
use App\Actions\Redirects\HandleRedirectResponseAction;
use App\Models\Redirect;
use App\Support\RouteFallbackResolvers\RedirectFallbackResolver;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RedirectFallbackResolverTest extends TestCase
{
    #[Test]
    public function itCallsTheCheckForRouteRedirectActionWhenSeeingIfTheFallbackCanBeHandled(): void
    {
        $this->mock(CheckForRouteRedirectAction::class)
            ->shouldReceive('handle')
            ->once();

        app(RedirectFallbackResolver::class)->canHandle(request());
    }

    #[Test]
    public function theCanHandleMethodReturnsFalseIfTheresNoRedirectToHandle(): void
    {
        $this->mock(CheckForRouteRedirectAction::class)
            ->shouldReceive('handle')
            ->andReturnNull()
            ->once();

        $this->assertFalse(app(RedirectFallbackResolver::class)->canHandle(request()));
    }

    #[Test]
    public function theCanHandleMethodReturnsTrueIfThereIsARedirect(): void
    {
        $redirect = $this->create(Redirect::class);

        $this->mock(CheckForRouteRedirectAction::class)
            ->shouldReceive('handle')
            ->andReturn($redirect)
            ->once();

        $this->assertTrue(app(RedirectFallbackResolver::class)->canHandle(request()));
    }

    #[Test]
    public function theHandleMethodCallsTheHandleRedirectResponseAction(): void
    {
        $redirect = $this->create(Redirect::class);

        $this->mock(HandleRedirectResponseAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($redirectArg) use ($redirect) {
                $this->assertTrue($redirect->is($redirectArg));

                return true;
            })
            ->andReturn(redirect()->to('/'))
            ->once();

        $instance = app(RedirectFallbackResolver::class);
        invade($instance)->redirect = $redirect;

        $instance->handle(request());
    }
}
