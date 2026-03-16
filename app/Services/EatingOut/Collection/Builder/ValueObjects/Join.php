<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Collection\Builder\ValueObjects;

use Illuminate\Database\Query\Builder;
use JsonSerializable;

class Join implements JsonSerializable
{
    public function __construct(protected string $table, protected string $first, protected mixed $operator = null, protected mixed $second = null)
    {
        //
    }

    public function __invoke(Builder $query): Builder
    {
        return $query->join($this->table, $this->first, $this->operator, $this->second);
    }

    public function jsonSerialize(): array
    {
        return [$this->table, $this->first, $this->operator, $this->second];
    }
}
