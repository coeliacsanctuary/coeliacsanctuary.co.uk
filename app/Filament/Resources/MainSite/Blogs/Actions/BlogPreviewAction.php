<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Actions;

use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use App\Models\NovaPreview;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BlogPreviewAction
{
    public static function make(): Action
    {
        $token = null;
        $errors = [];

        return Action::make('preview')
            ->label('Preview')
            ->icon(Heroicon::Eye)
            ->color('gray')
            ->modal()
            ->modalHeading('Blog Preview')
            ->modalWidth(Width::SevenExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->mountUsing(function (Get $schemaGet, ?Blog $record) use (&$token, &$errors): void {
                $token = null;
                $errors = [];

                $payload = static::buildPayload($schemaGet, $record);

                $errors = static::validatePayload($payload);

                if ($errors !== []) {
                    return;
                }

                $token = Str::uuid()->toString();

                NovaPreview::query()->create([
                    'model' => 'blog',
                    'token' => $token,
                    'payload' => $payload,
                ]);
            })
            ->modalContent(fn () => view('filament.actions.blog-preview', [
                'url' => $token ? route('nova-preview.show', $token) : null,
                'previewErrors' => $errors,
            ]));
    }

    /** @return array<string, mixed> */
    protected static function buildPayload(Get $schemaGet, ?Blog $record): array
    {
        $tags = collect($schemaGet('tags') ?? [])->filter()->values();

        return [
            'title' => $schemaGet('title'),
            'short_title' => $schemaGet('short_title'),
            'description' => $schemaGet('description'),
            'body' => $schemaGet('body'),
            'primary_image_url' => static::firstImageUrl($schemaGet('header'), $record, 'primary'),
            'social_image_url' => static::firstImageUrl($schemaGet('social'), $record, 'social'),
            'header_image_alt_text' => $schemaGet('header_image_alt_text'),
            'show_author' => (bool) ($schemaGet('show_author') ?? true),
            'tags' => $tags->all(),
            'primary_tag_id' => static::primaryTagName($schemaGet('primary_tag_id')),
            'faq_display' => $schemaGet('faq_display'),
            'faqs' => static::faqs($schemaGet('faqs')),
            'body_images' => static::bodyImages($schemaGet('body_images'), $record),
        ];
    }

    /**
     * `BlogRenderer::resolvePrimaryTag()` matches on the tag name, not the key, because
     * Nova's select stores the label. Filament stores the id, so resolve it back.
     */
    protected static function primaryTagName(mixed $primaryTagId): ?string
    {
        if (blank($primaryTagId)) {
            return null;
        }

        return BlogTag::query()->find($primaryTagId)?->tag;
    }

    /**
     * @param  mixed  $state
     * @return array<int, array{question: string, answer: string|null}>
     */
    protected static function faqs(mixed $state): array
    {
        return collect(is_array($state) ? $state : [])
            ->filter(fn (mixed $faq): bool => is_array($faq) && filled($faq['question'] ?? null))
            ->map(fn (array $faq): array => [
                'question' => $faq['question'],
                'answer' => $faq['answer'] ?? null,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  mixed  $state
     * @return array<int, array{file_name: string, url: string|null}>
     */
    protected static function bodyImages(mixed $state, ?Blog $record): array
    {
        $existingMedia = $record?->getMedia('body') ?? collect();

        return collect(is_array($state) ? $state : [])
            ->map(function (mixed $file) use ($existingMedia): ?array {
                if ($file instanceof TemporaryUploadedFile) {
                    return [
                        'file_name' => static::sanitiseFileName($file->getClientOriginalName()),
                        'url' => $file->isPreviewable() ? $file->temporaryUrl() : null,
                    ];
                }

                /** @var ?Media $media */
                $media = $existingMedia->firstWhere('uuid', $file);

                if ( ! $media) {
                    return null;
                }

                return ['file_name' => $media->file_name, 'url' => $media->getUrl()];
            })
            ->filter()
            ->values()
            ->all();
    }

    protected static function firstImageUrl(mixed $state, ?Blog $record, string $collection): ?string
    {
        $file = collect(is_array($state) ? $state : [])->first();

        if ($file instanceof TemporaryUploadedFile) {
            return $file->isPreviewable() ? $file->temporaryUrl() : null;
        }

        if (filled($file)) {
            /** @var ?Media $media */
            $media = $record?->getMedia($collection)->firstWhere('uuid', $file);

            if ($media) {
                return $media->getUrl('webp') ?: $media->getUrl();
            }
        }

        return $record?->getFirstMedia($collection)?->getUrl();
    }

    /**
     * Mirrors Spatie's FileAdder::defaultSanitizer() so the placeholder matches the
     * `file_name` the media library will assign once this file is persisted.
     */
    protected static function sanitiseFileName(string $fileName): string
    {
        $fileName = preg_replace('#\p{C}+#u', '', $fileName) ?? $fileName;

        return str_replace(['#', '/', '\\', ' '], '-', $fileName);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    protected static function validatePayload(array $payload): array
    {
        $required = [
            'title' => 'A title is required to preview.',
            'description' => 'A description is required to preview.',
            'body' => 'A body is required to preview.',
            'primary_image_url' => 'A header image is required to preview.',
        ];

        return collect($required)
            ->filter(fn (string $message, string $key): bool => blank($payload[$key] ?? null))
            ->values()
            ->all();
    }
}
