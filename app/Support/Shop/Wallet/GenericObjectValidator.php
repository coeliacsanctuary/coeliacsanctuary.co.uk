<?php

declare(strict_types=1);

namespace App\Support\Shop\Wallet;

use Spatie\LaravelMobilePass\Builders\Google\Validators\GenericObjectValidator as BaseGenericObjectValidator;

class GenericObjectValidator extends BaseGenericObjectValidator
{
    /** @return array<string, array<int, string>> */
    protected function rules(): array
    {
        return array_merge(parent::rules(), [
            'hexBackgroundColor' => ['nullable', 'string'],
            'logo' => ['nullable', 'array'],
            'heroImage' => ['nullable', 'array'],
        ]);
    }
}
