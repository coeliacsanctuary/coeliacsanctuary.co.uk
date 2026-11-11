<?php

declare(strict_types=1);

namespace Tests\Unit\Bootstrappers;

use App\Bootstrappers\RegisterExternalApiRoutes;
use App\Http\Api\V1\Middleware\ExternalApiSourceMiddleware;
use App\Http\Api\V1\Middleware\RequireApiBasicAuthMiddleware;
use App\Http\Middleware\RequireWebBasicAuthMiddleware;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterExternalApiRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['router']->setRoutes(new RouteCollection());

        RegisterExternalApiRoutes::handle();

        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    #[Test]
    public function itRegistersTheV1RoutesUnderTheV1PrefixAndName(): void
    {
        $route = $this->app['router']->getRoutes()->getByName('api.v1.blogs.index');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('api/v1/blogs', $route->uri());
    }

    #[Test]
    public function itAppliesTheExternalApiMiddleware(): void
    {
        $middleware = $this->middlewareFor('api.v1.blogs.index');

        $this->assertContains(RequireApiBasicAuthMiddleware::class, $middleware);
        $this->assertContains(ExternalApiSourceMiddleware::class, $middleware);
    }

    #[Test]
    public function itRemovesTheWebsiteBasicAuthMiddlewareFromTheApiGroup(): void
    {
        $this->assertContains(RequireWebBasicAuthMiddleware::class, $this->app['router']->getMiddlewareGroups()['api']);

        $this->assertNotContains(RequireWebBasicAuthMiddleware::class, $this->middlewareFor('api.v1.blogs.index'));
    }

    protected function middlewareFor(string $name): array
    {
        $route = $this->app['router']->getRoutes()->getByName($name);

        return $this->app['router']->gatherRouteMiddleware($route);
    }
}
