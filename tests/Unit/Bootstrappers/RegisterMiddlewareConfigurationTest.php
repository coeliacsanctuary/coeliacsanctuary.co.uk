<?php

declare(strict_types=1);

namespace Tests\Unit\Bootstrappers;

use App\Bootstrappers\RegisterMiddlewareConfiguration;
use App\Http\Middleware\AddRouteModelBindingFallbacksMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RequireWebBasicAuthMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use JourneyTracker\JourneyTrackerLaravel\Http\Middleware\LogPageViewMiddleware;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterMiddlewareConfigurationTest extends TestCase
{
    protected array $middlewareGroups;

    protected function setUp(): void
    {
        parent::setUp();

        AuthenticationException::redirectUsing(fn (): null => null);

        $this->middlewareGroups = RegisterMiddlewareConfiguration::handle(new Middleware())->getMiddlewareGroups();
    }

    #[Test]
    public function itPrependsTheBasicAuthAndRouteModelBindingFallbackMiddlewareToTheWebGroup(): void
    {
        $this->assertEquals(
            [RequireWebBasicAuthMiddleware::class, AddRouteModelBindingFallbacksMiddleware::class],
            array_slice($this->middlewareGroups['web'], 0, 2),
        );
    }

    #[Test]
    public function itAppendsTheInertiaAndPageViewMiddlewareToTheWebGroup(): void
    {
        $this->assertEquals(
            [HandleInertiaRequests::class, LogPageViewMiddleware::class],
            array_slice($this->middlewareGroups['web'], -2),
        );
    }

    #[Test]
    public function itPrependsTheWebsiteBasicAuthMiddlewareToTheApiGroup(): void
    {
        $this->assertEquals(RequireWebBasicAuthMiddleware::class, $this->middlewareGroups['api'][0]);
    }

    #[Test]
    public function itRedirectsGuestsOnMyTravelCardsToTheMyTravelCardsLogin(): void
    {
        $request = Request::create(route('my-travel-cards.login.show'));

        $this->assertEquals(route('my-travel-cards.login.show'), new AuthenticationException()->redirectTo($request));
    }

    #[Test]
    public function itRedirectsAllOtherGuestsToTheNovaLogin(): void
    {
        $request = Request::create('/foo');

        $this->assertEquals(route('nova.pages.login'), new AuthenticationException()->redirectTo($request));
    }
}
