<?php

declare(strict_types=1);

namespace Tests\Unit\Support\MagicRouting;

use App\Support\MagicRouting\RouteToController;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\MagicRouting\AnotherStubDependency;
use Tests\Fixtures\MagicRouting\StubController;
use Tests\Fixtures\MagicRouting\StubDependency;
use Tests\Fixtures\MagicRouting\StubMultiParamController;
use Tests\Fixtures\MagicRouting\StubResponse;
use Tests\TestCase;

class RouteToControllerTest extends TestCase
{
    #[Test]
    public function itCallsTheControllerWithParametersResolvedFromKnownDependencies(): void
    {
        $dep = new StubDependency();
        $controller = new StubController();

        app(RouteToController::class)->handle($controller, ['dep' => $dep]);

        $this->assertSame($dep, $controller->receivedDep);
    }

    #[Test]
    public function itResolvesParametersFromTheContainerWhenNotInKnownDependencies(): void
    {
        $dep = new StubDependency();
        $controller = new StubController();

        $this->app->instance(StubDependency::class, $dep);

        app(RouteToController::class)->handle($controller);

        $this->assertSame($dep, $controller->receivedDep);
    }

    #[Test]
    public function itMixesKnownDependenciesWithContainerResolvedParameters(): void
    {
        $dep = new StubDependency();
        $other = new AnotherStubDependency();
        $controller = new StubMultiParamController();

        $this->app->instance(AnotherStubDependency::class, $other);

        app(RouteToController::class)->handle($controller, ['dep' => $dep]);

        $this->assertSame($dep, $controller->receivedDep);
        $this->assertSame($other, $controller->receivedOther);
    }

    #[Test]
    public function itReturnsTheResponsableReturnedByTheController(): void
    {
        $response = new StubResponse();
        $controller = new StubController($response);

        $result = app(RouteToController::class)->handle($controller, ['dep' => new StubDependency()]);

        $this->assertSame($response, $result);
    }
}
