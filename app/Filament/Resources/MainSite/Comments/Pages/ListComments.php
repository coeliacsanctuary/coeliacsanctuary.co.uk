<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Comments\Pages;

use App\Filament\Resources\MainSite\Comments\CommentResource;
use Filament\Resources\Pages\ListRecords;

class ListComments extends ListRecords
{
    protected static string $resource = CommentResource::class;
}
