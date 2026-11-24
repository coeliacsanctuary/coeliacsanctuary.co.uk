<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Filament\Support\FindEateryLocation;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;

class LocationLookup extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Location Search');
        $this->placeholder('Search for a location...');
        $this->searchable();
        $this->allowHtml();
        $this->live();
        $this->dehydrated(false);
        $this->noSearchResultsMessage('No results found, try searching for the county or borough');

        $this->getSearchResultsUsing(app(FindEateryLocation::class)->handle(...));
        $this->getOptionLabelUsing(fn (): ?string => null);

        $this->afterStateUpdated(function (?string $state, Set $set, LocationLookup $component): void {
            if (blank($state)) {
                return;
            }

            [$countryId, $countyId, $townId, $areaId] = array_map(
                fn (string $id): ?int => $id === '' ? null : (int) $id,
                explode('|', $state),
            );

            $set('country_id', $countryId);
            $set('county_id', $countyId);
            $set('town_id', $townId);
            $set('area_id', $areaId);

            $component->state(null);
        });
    }

    public function getValidationRules(): array
    {
        return [];
    }
}
