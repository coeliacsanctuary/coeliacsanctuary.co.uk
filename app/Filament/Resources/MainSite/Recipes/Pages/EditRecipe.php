<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes\Pages;

use App\Filament\Resources\MainSite\Recipes\RecipeResource;
use Filament\Resources\Pages\EditRecord;

class EditRecipe extends EditRecord
{
    protected static string $resource = RecipeResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return RecipeResource::mutateForSave($data);
    }
}
