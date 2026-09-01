<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes\Pages;

use App\Filament\Resources\MainSite\Recipes\RecipeResource;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditRecipe extends BaseEditRecord
{
    protected static string $resource = RecipeResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return RecipeResource::mutateForSave($data);
    }
}
