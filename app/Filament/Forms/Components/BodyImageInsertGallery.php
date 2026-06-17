<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Get;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BodyImageInsertGallery extends Field
{
    protected string $view = 'filament.forms.components.body-image-insert-gallery';

    protected string $collection = 'body';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(false);
    }

    public function collection(string $collection): static
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * @return array<int, array{key: string, thumbnail: ?string, label: string, insertSrc: string, pending: bool}>
     */
    public function getGalleryItems(): array
    {
        $rawState = $this->evaluate(fn (Get $get) => $get('body_images')) ?? [];

        $record = $this->getRecord();
        $existingMedia = $record?->getMedia($this->collection) ?? collect();

        $items = [];

        foreach ($rawState as $key => $file) {
            if ($file instanceof TemporaryUploadedFile) {
                $items[] = [
                    'key' => (string) $key,
                    'thumbnail' => $file->isPreviewable() ? $file->temporaryUrl() : null,
                    'label' => $file->getClientOriginalName(),
                    'insertSrc' => $this->sanitiseFileName($file->getClientOriginalName()),
                    'pending' => true,
                ];

                continue;
            }

            /** @var ?Media $media */
            $media = $existingMedia->firstWhere('uuid', $file);

            if ( ! $media) {
                continue;
            }

            $items[] = [
                'key' => (string) $key,
                'thumbnail' => $media->getUrl(),
                'label' => $media->file_name,
                'insertSrc' => $media->file_name,
                'pending' => false,
            ];
        }

        return $items;
    }

    /**
     * Mirrors Spatie's FileAdder::defaultSanitizer() so the placeholder matches
     * the `file_name` the media library will assign once this file is persisted.
     */
    protected function sanitiseFileName(string $fileName): string
    {
        $fileName = preg_replace('#\p{C}+#u', '', $fileName) ?? $fileName;

        return str_replace(['#', '/', '\\', ' '], '-', $fileName);
    }
}
