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

    public function __invoke(Builder $query, ?string $table = null): Builder
    {
        $first = $table ? str_replace('[parent]', $table, $this->first) : $this->first;
        $operator = $table && $this->operator ? str_replace('[parent]', $table, $this->operator) : $this->operator;
        $second = $table && $this->second ? str_replace('[parent]', $table, $this->second) : $this->second;

        return $query->join($this->table, $first, $operator, $second);
    }

    public function jsonSerialize(): array
    {
        return [$this->table, $this->first, $this->operator, $this->second];
    }
}
