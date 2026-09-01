<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;

abstract class BaseEditRecord extends EditRecord
{
    protected bool $shouldReturnToEditPageAfterSaving = false;

    public function saveAndContinueEditing(): void
    {
        $this->shouldReturnToEditPageAfterSaving = true;

        $this->save();
    }

    /** @return array<Action | ActionGroup> */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getSaveAndContinueEditingFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSaveAndContinueEditingFormAction(): Action
    {
        return Action::make('saveAndContinueEditing')
            ->label('Save & continue editing')
            ->action('saveAndContinueEditing')
            ->color('gray')
            ->outlined();
    }

    protected function getRedirectUrl(): ?string
    {
        if ($this->shouldReturnToEditPageAfterSaving) {
            return $this->getResourceUrl('edit');
        }

        return parent::getRedirectUrl();
    }
}
