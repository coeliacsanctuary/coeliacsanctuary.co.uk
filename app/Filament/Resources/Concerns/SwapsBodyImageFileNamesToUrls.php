<?php

declare(strict_types=1);

namespace App\Filament\Resources\Concerns;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait SwapsBodyImageFileNamesToUrls
{
    protected function swapBodyImageFileNamesToUrls(HasMedia&Model $record, string $attribute = 'body', string $collection = 'body'): void
    {
        $body = $record->{$attribute};

        $record->getMedia($collection)->each(function (Media $media) use (&$body): void {
            $body = str_replace($media->file_name, $media->getUrl(), (string) $body);
        });

        if ($body !== $record->{$attribute}) {
            $record->updateQuietly([$attribute => $body]);
        }
    }
}
