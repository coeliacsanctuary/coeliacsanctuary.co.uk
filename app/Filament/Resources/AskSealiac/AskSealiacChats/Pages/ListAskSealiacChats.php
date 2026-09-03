<?php

declare(strict_types=1);

namespace App\Filament\Resources\AskSealiac\AskSealiacChats\Pages;

use App\Filament\Resources\AskSealiac\AskSealiacChats\AskSealiacChatResource;
use Filament\Resources\Pages\ListRecords;

class ListAskSealiacChats extends ListRecords
{
    protected static string $resource = AskSealiacChatResource::class;
}
