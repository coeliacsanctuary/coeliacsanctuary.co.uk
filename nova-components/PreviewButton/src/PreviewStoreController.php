<?php

declare(strict_types=1);

namespace Jpeters8889\PreviewButton;

use App\Models\NovaPreview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PreviewStoreController
{
    /** @var array<string, array<string, array<string>>> */
    protected array $validationRules = [
        'blog' => [
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'body' => ['required', 'string'],
            'primary_image_url' => ['required', 'string'],
            'social_image_url' => ['nullable', 'string'],
            'show_author' => ['nullable', 'boolean'],
            'body_images' => ['nullable', 'array'],
            'body_images.*.file_name' => ['required', 'string'],
            'body_images.*.url' => ['nullable', 'string'],
        ],
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'model' => ['required', 'string', Rule::in(array_keys($this->validationRules))],
        ]);

        $model = $request->string('model')->toString();

        $request->validate($this->validationRules[$model]);

        $preview = NovaPreview::query()->create([
            'model' => $model,
            'token' => Str::uuid()->toString(),
            'payload' => $request->only(array_filter(array_keys($this->validationRules[$model]), fn ($key) => ! str_contains($key, '.'))),
        ]);

        return response()->json([
            'token' => $preview->token,
            'url' => route('nova-preview.show', $preview->token),
        ]);
    }
}
