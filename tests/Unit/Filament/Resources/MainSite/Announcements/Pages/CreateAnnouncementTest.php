<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Announcements\Pages;

use App\Filament\Resources\MainSite\Announcements\AnnouncementResource;
use App\Filament\Resources\MainSite\Announcements\Pages\CreateAnnouncement;
use App\Models\Announcement;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateAnnouncementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itCreatesTheAnnouncement(): void
    {
        $this->assertDatabaseEmpty(Announcement::class);

        $this->createAnnouncement()->assertNotified();

        $this->assertDatabaseCount(Announcement::class, 1);

        $announcement = $this->createdAnnouncement();

        $this->assertSame('The shop is closed', $announcement->title);
        $this->assertSame('Orders are paused until Monday', $announcement->text);
        $this->assertTrue($announcement->live);
    }

    #[Test]
    public function itStoresTheExpiryDate(): void
    {
        $expiresAt = Carbon::now()->addWeeks(2);

        $this->createAnnouncement(['expires_at' => $expiresAt]);

        $this->assertTrue($expiresAt->isSameSecond($this->createdAnnouncement()->expires_at));
    }

    #[Test]
    public function itCreatesAnAnnouncementThatIsntLive(): void
    {
        $this->createAnnouncement(['live' => false]);

        $this->assertFalse($this->createdAnnouncement()->live);
    }

    #[Test]
    public function itSendsTheUserBackToTheAnnouncementListAfterCreating(): void
    {
        $this->createAnnouncement()->assertRedirect(AnnouncementResource::getUrl('index'));
    }

    protected function createAnnouncement(array $overrides = []): Testable
    {
        return Livewire::test(CreateAnnouncement::class)
            ->fillForm([
                'title' => 'The shop is closed',
                'text' => 'Orders are paused until Monday',
                'live' => true,
                'expires_at' => Carbon::now()->addWeek(),
                ...$overrides,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    protected function createdAnnouncement(): Announcement
    {
        return Announcement::query()->withoutGlobalScopes()->firstOrFail();
    }
}
