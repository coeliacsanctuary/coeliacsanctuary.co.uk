<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Filament\Support\FindEateryOrBranch;
use Filament\Forms\Components\Select;

class EateryOrBranchLookup extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Eatery or Branch');
        $this->placeholder('Search for an eatery or branch...');
        $this->searchable();

        $this->getSearchResultsUsing(app(FindEateryOrBranch::class)->handle(...));
        $this->getOptionLabelUsing(fn (string $value): ?string => app(FindEateryOrBranch::class)->label($value));
    }
}
