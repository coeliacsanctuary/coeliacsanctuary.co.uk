<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Menu;
use App\Filament\Support\EditRecordPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceEditRecordPageClassGenerator;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Js;
use Illuminate\Foundation\Vite;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentColor;
use Filament\Tables\Table;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->assets([
                Js::make('body-field', app(Vite::class)->asset('resources/js/filament/body-field.js')),
            ])
            ->font('Raleway')
            ->login()
            ->brandLogo('/images/logo.svg')
            ->colors([
                'primary' => '#80CCFC',
            ])
            ->maxContentWidth(Width::Full)
            ->resourceCreatePageRedirect('index')
            ->resourceEditPageRedirect('index')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigation(Menu::make(...))
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        $this->app->bind(ResourceEditRecordPageClassGenerator::class, EditRecordPageClassGenerator::class);

        FilamentColor::register([
            'primary' => '#80CCFC',
            'info' => '#DBBC25',
        ]);

        Table::configureUsing(function (Table $table): void {
            $table
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes())
                ->defaultPaginationPageOption(25);
        });

        SpatieMediaLibraryFileUpload::configureUsing(function (SpatieMediaLibraryFileUpload $component): void {
            $component->disk('media')->preserveFilenames();
        });

        if (Str::startsWith(Request::path(), 'admin')) {
            Model::automaticallyEagerLoadRelationships();
            Model::setAllGlobalScopes([]);
        }
    }
}
