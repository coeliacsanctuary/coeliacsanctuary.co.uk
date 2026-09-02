<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Announcements\Pages;

use App\Filament\Resources\MainSite\Announcements\AnnouncementResource;
use App\Filament\Resources\MainSite\Announcements\Pages\EditAnnouncement;
use App\Models\Announcement;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditAnnouncementTest extends TestCase
{
    protected Announcement $announcement;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));

        $this->announcement = $this->create(Announcement::class, [
            'title' => 'The shop is closed',
            'text' => 'Orders are paused until Monday',
            'live' => true,
            'expires_at' => Carbon::now()->addWeek(),
        ]);
    }

    #[Test]
    public function itFillsTheFormFromTheAnnouncement(): void
    {
        $this->editPage()->assertSchemaStateSet([
            'title' => 'The shop is closed',
            'text' => 'Orders are paused until Monday',
            'live' => true,
        ]);
    }

    #[Test]
    public function itUpdatesTheAnnouncement(): void
    {
        $this->editPage()
            ->fillForm([
                'title' => 'The shop is open again',
                'text' => 'Orders are being taken as normal',
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        $this->announcement->refresh();

        $this->assertSame('The shop is open again', $this->announcement->title);
        $this->assertSame('Orders are being taken as normal', $this->announcement->text);
    }

    #[Test]
    public function itTakesAnAnnouncementOffline(): void
    {
        $this->editPage()->fillForm(['live' => false])->call('save')->assertHasNoFormErrors();

        $this->assertFalse($this->announcement->refresh()->live);
    }

    #[Test]
    public function itPushesTheExpiryDateBack(): void
    {
        $expiresAt = Carbon::now()->addMonth();

        $this->editPage()->fillForm(['expires_at' => $expiresAt])->call('save')->assertHasNoFormErrors();

        $this->assertTrue($expiresAt->isSameSecond($this->announcement->refresh()->expires_at));
    }

    #[Test]
    public function itWontLetYouSaveAnExpiryDateInThePast(): void
    {
        $this->editPage()
            ->fillForm(['expires_at' => Carbon::now()->subWeek()])
            ->call('save')
            ->assertHasFormErrors(['expires_at' => 'after']);
    }

    #[Test]
    public function itCanOpenAnAnnouncementThatIsNotLive(): void
    {
        $announcement = $this->create(Announcement::class, ['live' => false]);

        Livewire::test(EditAnnouncement::class, ['record' => $announcement->getRouteKey()])->assertOk();
    }

    #[Test]
    public function itDoesNotLetYouDeleteAnAnnouncement(): void
    {
        $this->editPage()->assertActionDoesNotExist(DeleteAction::class);
    }

    #[Test]
    public function itSendsTheUserBackToTheAnnouncementListAfterSaving(): void
    {
        $this->editPage()->call('save')->assertRedirect(AnnouncementResource::getUrl('index'));
    }

    protected function editPage(): Testable
    {
        return Livewire::test(EditAnnouncement::class, ['record' => $this->announcement->getRouteKey()]);
    }
}
