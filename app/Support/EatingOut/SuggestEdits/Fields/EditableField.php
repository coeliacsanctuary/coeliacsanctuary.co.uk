<?php

declare(strict_types=1);

namespace App\Support\EatingOut\SuggestEdits\Fields;

use App\Models\EatingOut\Eatery;

abstract class EditableField
{
    final public function __construct(protected mixed $value = null)
    {
        //
    }

    public static function validationRules(): array
    {
        return ['required'];
    }

    public static function make(mixed $value = null): static
    {
        return new static($value);
    }

    protected function transformForSaving(): string|int|null
    {
        if (is_string($this->value)) {
            return $this->value;
        }

        if (is_int($this->value)) {
            return $this->value;
        }

        return null;
    }

    public function prepare(): string|int|null
    {
        return $this->transformForSaving();
    }

    abstract public function getCurrentValue(Eatery $eatery): ?string;

    abstract public function commitSuggestedValue(Eatery $eatery): void;

    public function getSuggestedValue(): string|int|null
    {
        return $this->transformForSaving();
    }
}
