<?php

declare(strict_types=1);

namespace App\Support\MagicRouting\Resolvers;

use App\Actions\EatingOut\MagicRouting\FindEateryMagicRouteRecordAction;
use App\Contracts\RegexRouteFallbackResolver;
use App\Contracts\RouteFallbackResolverContract;
use App\Enums\EatingOut\EateryMagicRouteType;
use App\Http\Controllers\ResolvedFallbacks\HundredPercentGlutenFreeEateries\CountyController;
use App\Http\Controllers\ResolvedFallbacks\HundredPercentGlutenFreeEateries\TownController;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Join;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use App\Services\EatingOut\Collection\Configuration;
use App\Support\MagicRouting\RouteToController;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use RuntimeException;

class HundredPercentGlutenFreeEateriesFallbackResolver implements RegexRouteFallbackResolver, RouteFallbackResolverContract
{
    public function canHandle(Request $request): bool
    {
        return Str::isMatch($this->regex(), $request->path());
    }

    protected function matchPattern(): string
    {
        return 'eating-100-percent-gluten-free-in-{location}';
    }

    protected function routeParameters(): array
    {
        return ['location'];
    }

    public function regex(): string
    {
        return Str::of($this->matchPattern())
            ->pipe(function (Stringable $string) {
                foreach ($this->routeParameters() as $parameter) {
                    $string = $string->replace('{' . $parameter . '}', '([a-z-]+)');
                }

                return $string;
            })
            ->prepend('/^')
            ->append('$/')
            ->toString();
    }

    public function generateRoutePath(array $parameters = []): string
    {
        return Str::of($this->matchPattern())
            ->pipe(function (Stringable $string) use ($parameters) {
                foreach ($parameters as $parameter => $value) {
                    throw_if(
                        ! in_array($parameter, $this->routeParameters()),
                        new RuntimeException("Unexpected route parameter `{$parameter}`")
                    );

                    $string = $string->replace('{' . $parameter . '}', $value);
                }

                return $string;
            })
            ->toString();
    }

    public function handle(Request $request): Responsable|RedirectResponse
    {
        $location = Str::match($this->regex(), $request->path());

        $routeRecord = app(FindEateryMagicRouteRecordAction::class)->handle(
            EateryMagicRouteType::HundredPercentGlutenFree,
            $location,
        );

        $controller = match ($routeRecord->location::class) {
            EateryCounty::class => CountyController::class,
            EateryTown::class => TownController::class,
            default => throw new RuntimeException('Unknown location type'),
        };

        return app(RouteToController::class)->handle(app($controller), [
            'routeRecord' => $routeRecord,
        ]);
    }
}
