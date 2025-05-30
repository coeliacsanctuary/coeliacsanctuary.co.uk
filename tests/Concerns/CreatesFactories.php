<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Database\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Factory as IlluminateFactory;
use Illuminate\Support\Collection;
use Tests\TestCase;

/** @mixin TestCase */
trait CreatesFactories
{
    /**
     * @template T
     *
     * @param  class-string<T>  $what
     * @return IlluminateFactory<T>
     */
    protected function build(string $what): IlluminateFactory
    {
        return Factory::factoryForModel($what);
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $what
     * @return ($countOrAttributes is positive-int ? Collection<int, T> : T)
     */
    protected function create(string $what, array|int $countOrAttributes = [], array $otherAttributes = [])
    {
        $count = is_array($countOrAttributes) ? 1 : $countOrAttributes;
        $attributes = is_array($countOrAttributes) ? $countOrAttributes : $otherAttributes;

        $factory = $this->build($what);

        if ($count > 1) {
            $factory = $factory->count($count);
        }

        return $factory->state($attributes)->create();
    }
}
