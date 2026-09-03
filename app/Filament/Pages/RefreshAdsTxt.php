<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\FetchAdsTxtAction;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class RefreshAdsTxt extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrench;

    protected static ?string $title = 'Refresh ads.txt';

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Action::make('refresh')
                    ->label('Reload ads.txt from mediavine')
                    ->action(fn (FetchAdsTxtAction $fetchAdsTxtAction) => $fetchAdsTxtAction->handle())
                    ->successNotificationTitle('ads.txt refreshed!'),
            ]),
        ]);
    }
}
