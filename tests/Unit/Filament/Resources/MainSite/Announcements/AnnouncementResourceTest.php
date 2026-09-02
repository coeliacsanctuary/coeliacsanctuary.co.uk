<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Announcements;

use App\Filament\Resources\MainSite\Announcements\AnnouncementResource;
use App\Models\Announcement;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AnnouncementResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itIncludesAnnouncementsThatArentLive(): void
    {
        $this->create(Announcement::class, ['live' => true]);
        $this->create(Announcement::class, ['live' => false]);

        $this->assertCount(2, AnnouncementResource::getEloquentQuery()->get());
    }

    #[Test]
    public function itIncludesAnnouncementsThatHaveExpired(): void
    {
        $this->create(Announcement::class);
        $this->build(Announcement::class)->expired()->create();

        $this->assertCount(2, AnnouncementResource::getEloquentQuery()->get());
    }

    #[Test]
    public function itTitlesAnAnnouncementByItsTitle(): void
    {
        $this->assertSame('title', AnnouncementResource::getRecordTitleAttribute());
    }

    #[Test]
    public function itIsNotGloballySearchable(): void
    {
        $this->assertFalse(AnnouncementResource::canGloballySearch());
    }

    #[Test]
    public function itRegistersTheListCreateAndEditPages(): void
    {
        $this->assertSame(['index', 'create', 'edit'], array_keys(AnnouncementResource::getPages()));
    }
}
