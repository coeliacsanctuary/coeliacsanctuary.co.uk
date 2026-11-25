<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryTowns\Pages;

use App\Filament\Resources\EatingOut\EateryTowns\EateryTownResource;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Url;

class CreateEateryTown extends CreateRecord
{
    protected static string $resource = EateryTownResource::class;

    #[Url]
    public ?int $county = null;

    protected function fillForm(): void
    {
        parent::fillForm();

        if ($this->county !== null) {
            $this->data['county_id'] = $this->county;
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            ...$data,
            'legacy' => '',
        ];
    }
}
