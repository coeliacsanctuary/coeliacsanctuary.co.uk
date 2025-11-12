<?php

declare(strict_types=1);

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:3', 'max:255'],
            'blogs' => ['bool'],
            'recipes' => ['bool'],
            'eateries' => ['bool'],
            'shop' => ['bool'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'blogs' => $this->string('blogs', 'false')->toString() === 'true',
            'recipes' => $this->string('recipes', 'false')->toString() === 'true',
            'eateries' => $this->string('eateries', 'false')->toString() === 'true',
            'shop' => $this->string('shop', 'false')->toString() === 'true',
        ]);
    }
}
