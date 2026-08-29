<?php

declare(strict_types=1);

namespace App\Support\MagicRouting;

use Illuminate\Contracts\Support\Responsable;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class RouteToController
{
    /** @param callable&object $controller */
    public function handle(callable $controller, array $knownDependencies = []): Responsable
    {
        $reflectedHandler = new ReflectionMethod($controller, '__invoke');
        $parameters = $reflectedHandler->getParameters();

        $resolvedParameters = collect($parameters)->map(function (ReflectionParameter $parameter) use ($knownDependencies) {
            if (array_key_exists($parameter->getName(), $knownDependencies)) {
                return $knownDependencies[$parameter->getName()];
            }

            $reflectionType = $parameter->getType();

            /** @var class-string $type */
            $type = $reflectionType instanceof ReflectionNamedType ? $reflectionType->getName() : null;

            return app($type);
        });

        return $controller(...$resolvedParameters->values()->all());
    }
}
