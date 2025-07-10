<?php

declare(strict_types=1);

namespace App\Http\Requests\EatingOut;

use App\Models\EatingOut\Eatery;
use Illuminate\Foundation\Http\FormRequest;

class EateryCreateReviewRequest extends FormRequest
{
    protected function isNationwide(): bool
    {
        /** @var Eatery $eatery */
        $eatery = $this->route('eatery');

        return $eatery->county?->county === 'Nationwide';
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'name' => $this->wantsJson() ? ['nullable', 'required_with:email,comment'] : ['nullable', 'required', 'string'],
            'email' => $this->wantsJson() ? ['nullable', 'required_with:name,comment', 'email'] : ['nullable', 'required', 'email'],
            'review' => $this->wantsJson() ? ['nullable', 'required_with:name,email'] : ['nullable', 'required', 'string', ! $this->user() ? 'max:1500' : ''],
            'food_rating' => ['nullable', 'in:poor,good,excellent'],
            'service_rating' => ['nullable', 'in:poor,good,excellent'],
            'how_expensive' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'images' => ['array', 'max:6'],
            'images.*' => ['string', 'exists:temporary_file_uploads,id'],
            'method' => ['in:website,app'],
            'admin_review' => $this->user() ? ['boolean'] : ['missing'],
            'branch_name' => $this->isNationwide() ? ['nullable', 'required', 'string'] : ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $map = [
            'food' => 'food_rating',
            'service' => 'service_rating',
            'expense' => 'how_expensive',
            'comment' => 'review',
        ];

        foreach ($map as $legacy => $new) {
            if ($this->has($legacy) && $this->missing($new)) {
                $this->merge([$new => $this->input($legacy)]);
            }
        }
    }

    public function shouldReviewBeApproved(): bool
    {
        //        if ($this->boolean('admin_review') === true && $this->user()?->isAdmin()) {
        if ($this->boolean('admin_review')) {
            return true;
        }

        if ($this->wantsJson()) {
            $requiredFieldsCheck = $this->string('name')->toString() === ''
                && $this->string('email')->toString() === ''
                && $this->string('review')->toString() === '';

            if ($this->isNationwide()) {
                return $this->string('branch_name')->toString() !== '' && $requiredFieldsCheck;
            }

            return $requiredFieldsCheck;
        }

        return true;
    }
}
