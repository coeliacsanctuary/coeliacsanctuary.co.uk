<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\MainSite\Announcements\AnnouncementResource;
use App\Models\Announcement;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AnnouncementPanelAccessTest extends TestCase
{
    protected Announcement $announcement;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->announcement = $this->create(Announcement::class);
    }

    #[Test]
    #[DataProvider('announcementPages')]
    public function guestsAreSentToTheLoginPage(string $page): void
    {
        $this->get($this->url($page))->assertRedirect(Filament::getLoginUrl());
    }

    #[Test]
    #[DataProvider('announcementPages')]
    public function signedInUsersCanOpenEveryAnnouncementPage(string $page): void
    {
        $this->actingAs($this->create(User::class))
            ->get($this->url($page))
            ->assertOk();
    }

    public static function announcementPages(): array
    {
        return [
            'the announcement list' => ['index'],
            'the create page' => ['create'],
            'the edit page' => ['edit'],
        ];
    }

    protected function url(string $page): string
    {
        return in_array($page, ['index', 'create'], true)
            ? AnnouncementResource::getUrl($page)
            : AnnouncementResource::getUrl($page, ['record' => $this->announcement]);
    }
}
