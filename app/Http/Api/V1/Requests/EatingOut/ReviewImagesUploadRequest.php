<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Requests\EatingOut;

use Illuminate\Foundation\Http\FormRequest;

class ReviewImagesUploadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'max:6'],
            'images.*' => ['file', 'image', 'mimetypes:image/jpg,image/jpeg,image/png,image/gif', 'max:5120'],
        ];
    }
}
