<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Collection\Builder\ValueObjects;

use JsonSerializable;

class Count implements JsonSerializable
{
    public function __construct(public string $table, public string $localKey, public string $foreignKey, public string $alias, public string $operator, public int $value)
    {
        //
    }

    public function jsonSerialize(): array
    {
        return [$this->table, $this->localKey, $this->foreignKey, $this->alias, $this->operator, $this->value];
    }
}
