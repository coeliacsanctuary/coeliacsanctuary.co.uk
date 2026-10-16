<?php

declare(strict_types=1);

namespace App\Rules\Shop;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ReviewProductIsDistinctRule implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $row = Str::of($attribute)->beforeLast('.');

        $index = (int) $row->afterLast('.')->toString();
        $type = Arr::get($this->data, $row->append('.type')->toString());

        $alreadyReviewed = collect(Arr::get($this->data, 'products', []))
            ->take($index)
            ->map(fn (mixed $product) => Arr::get($product, 'type') . ':' . Arr::get($product, 'id'))
            ->contains("{$type}:{$value}");

        if ($alreadyReviewed) {
            $fail('You\'ve already left a review for this product');
        }
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
