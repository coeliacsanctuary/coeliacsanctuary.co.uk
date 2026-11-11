<?php

declare(strict_types=1);

namespace Tests\Unit\Bootstrappers;

use App\Bootstrappers\RegisterLocalDevRoutes;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterLocalDevRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['router']->setRoutes(new RouteCollection());

        RegisterLocalDevRoutes::handle();
    }

    #[Test]
    public function itRegistersTheLocalRoutesUnderTheDevPrefix(): void
    {
        $routes = collect($this->app['router']->getRoutes()->getRoutes());

        $this->assertNotEmpty($routes);
        $this->assertTrue($routes->every(fn (Route $route): bool => str_starts_with($route->uri(), '__dev/')));
    }

    #[Test]
    public function itRegistersTheLocalRoutesOnTheWebMiddlewareGroup(): void
    {
        $routes = collect($this->app['router']->getRoutes()->getRoutes());

        $this->assertTrue($routes->every(fn (Route $route): bool => in_array('web', $route->middleware(), true)));
    }
}
