<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Collection\Builder\ValueObjects;

use JsonSerializable;

class Order implements JsonSerializable
{
    public function __construct(public string $column, public string $direction, public ?string $table = null, public ?string $localKey = null, public ?string $foreignKey = null)
    {
        //
    }

    public function jsonSerialize(): array
    {
        return [$this->column, $this->direction, $this->table, $this->localKey, $this->foreignKey];
    }
}
