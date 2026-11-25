<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAreas\Pages;

use App\Filament\Resources\EatingOut\EateryAreas\EateryAreaResource;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Url;

class CreateEateryArea extends CreateRecord
{
    protected static string $resource = EateryAreaResource::class;

    #[Url]
    public ?int $borough = null;

    protected function fillForm(): void
    {
        parent::fillForm();

        if ($this->borough !== null) {
            $this->data['town_id'] = $this->borough;
        }
    }
}
