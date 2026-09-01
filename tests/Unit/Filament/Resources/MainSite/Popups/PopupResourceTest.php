<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Popups;

use App\Filament\Resources\MainSite\Popups\PopupResource;
use App\Models\Popup;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PopupResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itIncludesPopupsThatArentLive(): void
    {
        $this->create(Popup::class, ['live' => true]);
        $this->create(Popup::class, ['live' => false]);

        $this->assertCount(2, PopupResource::getEloquentQuery()->get());
    }

    #[Test]
    public function itCallsAPopupASitePopup(): void
    {
        $this->assertSame('Site Popup', PopupResource::getModelLabel());
    }

    #[Test]
    public function itCallsTheResourceSitePopups(): void
    {
        $this->assertSame('Site Popups', PopupResource::getTitleCasePluralModelLabel());
        $this->assertSame('Site Popups', PopupResource::getNavigationLabel());
    }

    #[Test]
    public function itIsNotGloballySearchable(): void
    {
        $this->assertNull(PopupResource::getRecordTitleAttribute());
        $this->assertFalse(PopupResource::canGloballySearch());
    }

    #[Test]
    public function itRegistersTheListCreateAndEditPages(): void
    {
        $this->assertSame(['index', 'create', 'edit'], array_keys(PopupResource::getPages()));
    }
}
