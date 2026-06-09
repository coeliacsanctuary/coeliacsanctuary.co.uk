<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Collection\Builder\ValueObjects;

use JsonSerializable;

class Average implements JsonSerializable
{
    public function __construct(public string $table, public string $column, public string $localKey, public string $foreignKey, public string $alias, public string $operator, public int|float $value)
    {
        //
    }

    public function jsonSerialize(): array
    {
        return [$this->table, $this->column, $this->localKey, $this->foreignKey, $this->alias, $this->operator, $this->value];
    }
}
