<?php

declare(strict_types=1);

namespace App\Http\Requests\EatingOut;

use Illuminate\Foundation\Http\FormRequest;

class EateryCreateReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'details' => ['required', 'string'],
            'branch_id' => ['nullable', 'bail', 'numeric', 'exists:wheretoeat_nationwide_branches,id'],
            'branch_name' => ['nullable', 'string'],
        ];
    }
}
