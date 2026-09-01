<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceEditRecordPageClassGenerator;

class EditRecordPageClassGenerator extends ResourceEditRecordPageClassGenerator
{
    public function getExtends(): string
    {
        return BaseEditRecord::class;
    }
}
