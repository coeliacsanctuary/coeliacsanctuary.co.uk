<?php

declare(strict_types=1);

namespace App\Filament\Resources\Concerns;

trait DeletesBodyImages
{
    public function deleteBodyImage(string $fileName, string $collection = 'body'): void
    {
        $record = $this->getRecord();

        $bodyContent = $this->form->getRawState()['body'] ?? '';

        if (str_contains((string) $bodyContent, $fileName)) {
            return;
        }

        $record->getMedia($collection)
            ->firstWhere('file_name', $fileName)
            ?->delete();

        $this->fillForm();
    }
}
