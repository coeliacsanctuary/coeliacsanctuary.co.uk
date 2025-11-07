<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Requests\EatingOut;

use App\Models\EatingOut\EaterySuggestedEdit;
use App\Support\EatingOut\SuggestEdits\SuggestedEditProcessor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class SuggestEditRequest extends FormRequest
{
    /*** @return class-string<SuggestedEditProcessor> | null */
    protected function resolveProcessorClass(): ?string
    {
        /** @var class-string<SuggestedEditProcessor> | null $processor */
        $processor = Arr::get(EaterySuggestedEdit::processorMaps(), $this->string('field')->toString());

        return $processor;
    }

    public function rules(): array
    {
        $rules = ['field' => ['required', Rule::in($this->editableFields())]];

        if ($this->resolveProcessorClass()) {
            $additionalRules = $this->resolveProcessorClass()::validationRules();

            if (array_key_first($additionalRules) === 'value') {
                return array_merge($rules, $additionalRules);
            }

            $rules['value'] = $additionalRules;
        }

        return $rules;
    }

    protected function editableFields(): array
    {
        return array_keys(EaterySuggestedEdit::processorMaps());
    }
}
