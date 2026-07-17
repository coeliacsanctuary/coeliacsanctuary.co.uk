<?php

declare(strict_types=1);

namespace Tests\Unit\Support\MagicRouting\Resolvers;

use App\Actions\EatingOut\MagicRouting\FindEateryMagicRouteRecordAction;
use App\Enums\EatingOut\EateryMagicRouteType;
use App\Http\Controllers\ResolvedFallbacks\HundredPercentGlutenFreeEateries\CountyController;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Join;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use App\Services\EatingOut\Collection\Configuration;
use App\Support\MagicRouting\Resolvers\HundredPercentGlutenFreeEateriesFallbackResolver;
use App\Support\MagicRouting\RouteToController;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\Fixtures\MagicRouting\StubResponse;
use Tests\TestCase;

class HundredPercentGlutenFreeEateriesFallbackResolverTest extends TestCase
{
    #[Test]
    public function itReturnsTrueWhenThePathMatchesTheExpectedPattern(): void
    {
        $request = Request::create('/eating-100-percent-gluten-free-in-london');

        $this->assertTrue(app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->canHandle($request));
    }

    #[Test]
    public function itReturnsFalseWhenThePathDoesNotMatchTheExpectedPattern(): void
    {
        $request = Request::create('/eating-out/london');

        $this->assertFalse(app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->canHandle($request));
    }

    #[Test]
    public function itReturnsTheCorrectRegexString(): void
    {
        $this->assertSame(
            '/^eating-100-percent-gluten-free-in-([a-z-]+)$/',
            app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->regex(),
        );
    }

    #[Test]
    public function itGeneratesTheCorrectPathForASingleWordLocation(): void
    {
        $this->assertSame(
            'eating-100-percent-gluten-free-in-london',
            app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->generateRoutePath(['location' => 'london']),
        );
    }

    #[Test]
    public function itGeneratesTheCorrectPathForAHyphenatedLocation(): void
    {
        $this->assertSame(
            'eating-100-percent-gluten-free-in-north-yorkshire',
            app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->generateRoutePath(['location' => 'north-yorkshire']),
        );
    }

    #[Test]
    public function itThrowsARuntimeExceptionWhenAnUnexpectedParameterIsPassed(): void
    {
        $this->expectException(RuntimeException::class);

        app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->generateRoutePath(['county' => 'london']);
    }

    #[Test]
    public function itCallsTheFindEateryMagicRouteRecordActionWithTheHundredPercentGlutenFreeType(): void
    {
        $routeRecord = $this->makeRouteRecordWithCounty();

        $this->mock(FindEateryMagicRouteRecordAction::class)
            ->shouldReceive('handle')
            ->withArgs(function (EateryMagicRouteType $type) {
                $this->assertSame(EateryMagicRouteType::HundredPercentGlutenFree, $type);

                return true;
            })
            ->andReturn($routeRecord)
            ->once();

        $this->mockRouteToController();

        app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->handle(
            Request::create('/eating-100-percent-gluten-free-in-london')
        );
    }

    #[Test]
    public function itExtractsAndPassesTheLocationSlugFromThePath(): void
    {
        $routeRecord = $this->makeRouteRecordWithCounty();

        $this->mock(FindEateryMagicRouteRecordAction::class)
            ->shouldReceive('handle')
            ->withArgs(function (EateryMagicRouteType $type, string $location) {
                $this->assertSame('london', $location);

                return true;
            })
            ->andReturn($routeRecord)
            ->once();

        $this->mockRouteToController();

        app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->handle(
            Request::create('/eating-100-percent-gluten-free-in-london')
        );
    }

    #[Test]
    public function itPassesAConfigurationCallableToTheFindEateryMagicRouteRecordAction(): void
    {
        $routeRecord = $this->makeRouteRecordWithCounty();

        $this->mock(FindEateryMagicRouteRecordAction::class)
            ->shouldReceive('handle')
            ->withArgs(function (EateryMagicRouteType $type, string $location, mixed $callback) {
                $this->assertIsCallable($callback);

                return true;
            })
            ->andReturn($routeRecord)
            ->once();

        $this->mockRouteToController();

        app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->handle(
            Request::create('/eating-100-percent-gluten-free-in-london')
        );
    }

    #[Test]
    public function theBuildConfigurationCallbackAddsTheExpectedJoinToTheConfiguration(): void
    {
        $routeRecord = $this->makeRouteRecordWithCounty();
        $capturedCallback = null;

        $this->mock(FindEateryMagicRouteRecordAction::class)
            ->shouldReceive('handle')
            ->withArgs(function (EateryMagicRouteType $type, string $location, mixed $callback) use (&$capturedCallback) {
                $capturedCallback = $callback;

                return true;
            })
            ->andReturn($routeRecord);

        $this->mockRouteToController();

        app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->handle(
            Request::create('/eating-100-percent-gluten-free-in-london')
        );

        $result = $capturedCallback(app(Configuration::class));

        $joins = $result->getJoins();

        $this->assertCount(1, $joins);
        $this->assertSame(
            ['wheretoeat_assigned_features', 'wheretoeat_assigned_features.wheretoeat_id', 'wheretoeat.id', null],
            $joins->first()->jsonSerialize(),
        );
    }

    #[Test]
    public function theBuildConfigurationCallbackAddsTheExpectedWhereToTheConfiguration(): void
    {
        $routeRecord = $this->makeRouteRecordWithCounty();
        $capturedCallback = null;

        $this->mock(FindEateryMagicRouteRecordAction::class)
            ->shouldReceive('handle')
            ->withArgs(function (EateryMagicRouteType $type, string $location, mixed $callback) use (&$capturedCallback) {
                $capturedCallback = $callback;

                return true;
            })
            ->andReturn($routeRecord);

        $this->mockRouteToController();

        app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->handle(
            Request::create('/eating-100-percent-gluten-free-in-london')
        );

        $result = $capturedCallback(app(Configuration::class));

        $wheres = $result->getWheres();

        $this->assertCount(1, $wheres);
        $this->assertSame(
            ['wheretoeat_assigned_features.feature_id', '=', 1, 'and'],
            $wheres->first()->jsonSerialize(),
        );
    }

    #[Test]
    public function itUsesTheCountyControllerWhenTheRouteRecordLocationIsACounty(): void
    {
        $routeRecord = $this->makeRouteRecordWithCounty();

        $this->mock(FindEateryMagicRouteRecordAction::class)
            ->shouldReceive('handle')
            ->andReturn($routeRecord);

        $countyController = $this->mock(CountyController::class);

        $this->mock(RouteToController::class)
            ->shouldReceive('handle')
            ->withArgs(function (mixed $controller) use ($countyController) {
                $this->assertSame($countyController, $controller);

                return true;
            })
            ->andReturn(new StubResponse())
            ->once();

        app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->handle(
            Request::create('/eating-100-percent-gluten-free-in-london')
        );
    }

    #[Test]
    public function itPassesTheRouteRecordToRouteToControllerAsAKnownDependency(): void
    {
        $routeRecord = $this->makeRouteRecordWithCounty();

        $this->mock(FindEateryMagicRouteRecordAction::class)
            ->shouldReceive('handle')
            ->andReturn($routeRecord);

        $this->mock(CountyController::class);

        $this->mock(RouteToController::class)
            ->shouldReceive('handle')
            ->withArgs(function (mixed $controller, array $knownDependencies) use ($routeRecord) {
                $this->assertSame($routeRecord, $knownDependencies['routeRecord']);

                return true;
            })
            ->andReturn(new StubResponse())
            ->once();

        app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->handle(
            Request::create('/eating-100-percent-gluten-free-in-london')
        );
    }

    protected function makeRouteRecordWithCounty(): EateryMagicRouteRecord
    {
        $routeRecord = $this->build(EateryMagicRouteRecord::class)->make();
        $routeRecord->setRelation('location', new EateryCounty());

        return $routeRecord;
    }

    protected function mockRouteToController(): void
    {
        $this->mock(RouteToController::class)
            ->shouldReceive('handle')
            ->andReturn(new StubResponse());
    }
}
