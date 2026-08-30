<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BodyImageInsertGallery extends Field
{
    protected string $view = 'filament.forms.components.body-image-insert-gallery';

    protected string $collection = 'body';

    protected string $bodyAttribute = 'body';

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

    public function bodyAttribute(string $attribute): static
    {
        $this->bodyAttribute = $attribute;

        return $this;
    }

    public function getCollection(): string
    {
        return $this->collection;
    }

    #[ExposedLivewireMethod]
    public function deleteBodyImage(string $fileName, string $collection): void
    {
        $record = $this->getRecord();

        $bodyContent = $this->getLivewire()->form->getRawState()[$this->bodyAttribute] ?? '';

        if (str_contains((string) $bodyContent, $fileName)) {
            return;
        }

        $record->getMedia($collection)
            ->firstWhere('file_name', $fileName)
            ?->delete();

        $this->getLivewire()->refreshFormData(['body_images']);
    }

    /**
     * @return array<int, array{key: string, thumbnail: ?string, label: string, insertSrc: string, pending: bool, isDeletable: bool, collection: string}>
     */
    public function getGalleryItems(): array
    {
        $rawState = $this->evaluate(fn (Get $get) => $get('body_images')) ?? [];
        $bodyContent = $this->evaluate(fn (Get $get) => $get('body')) ?? '';

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
                    'isDeletable' => false,
                    'collection' => $this->collection,
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
                'isDeletable' => ! str_contains((string) $bodyContent, $media->file_name),
                'collection' => $this->collection,
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
