<?php

declare(strict_types=1);

namespace App\Filament\Resources\AskSealiac\AskSealiacChats;

use App\Filament\Resources\AskSealiac\AskSealiacChats\Pages\ListAskSealiacChats;
use App\Filament\Resources\AskSealiac\AskSealiacChats\Tables\AskSealiacChatsTable;
use App\Filament\Resources\BaseResource;
use App\Models\AskSealiacChat;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AskSealiacChatResource extends BaseResource
{
    protected static ?string $model = AskSealiacChat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $modelLabel = 'Chat';

    protected static ?string $pluralModelLabel = 'Chats';

    protected static bool $isGloballySearchable = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('messages');
    }

    public static function table(Table $table): Table
    {
        return AskSealiacChatsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAskSealiacChats::route('/'),
        ];
    }
}
