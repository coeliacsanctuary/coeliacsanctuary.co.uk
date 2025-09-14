<?php

declare(strict_types=1);

namespace Jpeters8889\PolymorphicPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface PolymorphicResource
{
    public function fields(): array;

    public function relationship(): string;

    /**
     * @param string $key
     * @param Collection<int, Model> $relationship
     */
    public function check(string $key, Collection $relationship, Model $resource): bool;

    /**
     * @param Collection<string, Model> $values
     * @param Model $model
     */
    public function set(Collection $values, Model $model): void;
}
