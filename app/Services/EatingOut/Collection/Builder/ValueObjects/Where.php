<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Collection\Builder\ValueObjects;

use Illuminate\Database\Query\Builder;
use JsonSerializable;

class Where implements JsonSerializable
{
    public function __construct(protected string $key, protected string $operator, protected mixed $value, protected string $boolean = 'and')
    {
        //
    }

    public function __invoke(Builder $query, ?string $table = null): Builder
    {
        $key = $table ? str_replace('[parent]', $table, $this->key) : $this->key;

        return $query->where($key, $this->operator, $this->value, $this->boolean);
    }

    public function jsonSerialize(): array
    {
        return [$this->key, $this->operator, $this->value, $this->boolean];
    }
}
