<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Rules\ValidArticleHtmlRule;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Events\RecordSaved;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Body extends Textarea
{
    protected string $view = 'filament.forms.components.body';

    protected bool $hasToolbar = true;

    protected bool $hasImages = false;

    protected string $imagesCollection = 'body';

    protected function setUp(): void
    {
        parent::setUp();

        $this->rows(25);
    }

    public function toolbar(bool $condition = true): static
    {
        $this->hasToolbar = $condition;

        return $this;
    }

    public function hasToolbar(): bool
    {
        return $this->hasToolbar;
    }

    public function validHtml(): static
    {
        return $this->rules([new ValidArticleHtmlRule()]);
    }

    public function images(string $collection = 'body'): static
    {
        $this->hasImages = true;
        $this->imagesCollection = $collection;

        $this->childComponents([
            SpatieMediaLibraryFileUpload::make('body_images')
                ->hiddenLabel()
                ->collection($collection)
                ->multiple()
                ->image()
                ->panelLayout('grid')
                ->itemPanelAspectRatio(0.5)
                ->appendFiles()
                ->imagePreviewHeight('176px'),

            BodyImageInsertGallery::make('body_images_gallery')
                ->collection($collection)
                ->bodyAttribute($this->getName()),
        ], 'bodyImages');

        $this->afterStateHydrated(static function (Body $component, mixed $state): void {
            $record = $component->getRecord();

            if ( ! $record || ! is_string($state) || blank($state)) {
                return;
            }

            $record->getMedia($component->getImagesCollection())->each(function (Media $media) use (&$state): void {
                $state = str_replace($media->getUrl(), $media->file_name, $state);
            });

            $component->state($state);
        });

        $attribute = $this->getName();

        app(Dispatcher::class)->listen(RecordSaved::class, function ($record) use ($collection, $attribute): void {
            if ( ! $record instanceof HasMedia || blank($record->{$attribute})) {
                return;
            }

            $body = $record->{$attribute};

            $record->unsetRelation('media');

            $record->getMedia($collection)->each(function (Media $media) use (&$body): void {
                $body = str_replace($media->file_name, $media->getUrl(), (string) $body);
            });

            if ($body !== $record->{$attribute}) {
                $record->updateQuietly([$attribute => $body]);
            }
        });

        return $this;
    }

    public function hasImages(): bool
    {
        return $this->hasImages;
    }

    public function getImagesCollection(): string
    {
        return $this->imagesCollection;
    }

    protected function makeChildSchema(string $key): Schema
    {
        if ($key !== 'bodyImages') {
            return parent::makeChildSchema($key);
        }

        return Schema::make($this->getLivewire())
            ->record(fn (): Model | array | null => $this->getRecord())
            ->statePath($this->getContainer()->getStatePath());
    }
}
