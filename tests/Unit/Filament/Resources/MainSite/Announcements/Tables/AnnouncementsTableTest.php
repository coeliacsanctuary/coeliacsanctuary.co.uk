<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Announcements\Tables;

use App\Filament\Resources\MainSite\Announcements\AnnouncementResource;
use App\Filament\Resources\MainSite\Announcements\Pages\ListAnnouncements;
use App\Models\Announcement;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AnnouncementsTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itShowsEveryAnnouncementWhateverItsLiveState(): void
    {
        $live = $this->create(Announcement::class, ['live' => true]);
        $notLive = $this->create(Announcement::class, ['live' => false]);

        Livewire::test(ListAnnouncements::class)->assertCanSeeTableRecords([$live, $notLive]);
    }

    #[Test]
    public function itShowsExpiredAnnouncements(): void
    {
        $current = $this->create(Announcement::class);
        $expired = $this->build(Announcement::class)->expired()->create();

        Livewire::test(ListAnnouncements::class)->assertCanSeeTableRecords([$current, $expired]);
    }

    #[Test]
    public function itShowsTheNewestAnnouncementsFirst(): void
    {
        $announcements = $this->create(Announcement::class, 3);

        Livewire::test(ListAnnouncements::class)
            ->assertCanSeeTableRecords($announcements->reverse()->values(), inOrder: true);
    }

    #[Test]
    #[DataProvider('columns')]
    public function itShowsTheAnnouncementColumns(string $column): void
    {
        $this->create(Announcement::class);

        Livewire::test(ListAnnouncements::class)->assertTableColumnExists($column);
    }

    public static function columns(): array
    {
        return [
            'id' => ['id'],
            'title' => ['title'],
            'live' => ['live'],
            'expired' => ['expired'],
            'expires at' => ['expires_at'],
        ];
    }

    #[Test]
    public function itLabelsTheIdColumn(): void
    {
        Livewire::test(ListAnnouncements::class)
            ->assertTableColumnExists('id', fn (TextColumn $column): bool => $column->getLabel() === 'ID');
    }

    #[Test]
    #[DataProvider('searchableColumns')]
    public function itSearchesTheAnnouncementColumns(string $column): void
    {
        Livewire::test(ListAnnouncements::class)
            ->assertTableColumnExists($column, fn (TextColumn $c): bool => $c->isSearchable());
    }

    public static function searchableColumns(): array
    {
        return [
            'id' => ['id'],
            'title' => ['title'],
        ];
    }

    #[Test]
    public function itDoesNotShowTheAnnouncementText(): void
    {
        $this->create(Announcement::class);

        Livewire::test(ListAnnouncements::class)->assertTableColumnDoesNotExist('text');
    }

    #[Test]
    public function itFindsAnAnnouncementByTitle(): void
    {
        $wanted = $this->create(Announcement::class, ['title' => 'The shop is closed']);
        $other = $this->create(Announcement::class, ['title' => 'New recipes added']);

        Livewire::test(ListAnnouncements::class)
            ->searchTable('The shop is closed')
            ->assertCanSeeTableRecords([$wanted])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itFindsAnAnnouncementByText(): void
    {
        $wanted = $this->create(Announcement::class, ['text' => 'Orders are paused until Monday']);
        $other = $this->create(Announcement::class, ['text' => 'Nothing to see here']);

        Livewire::test(ListAnnouncements::class)
            ->searchTable('Orders are paused until Monday')
            ->assertCanSeeTableRecords([$wanted])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itFindsAnAnnouncementById(): void
    {
        $announcements = $this->create(Announcement::class, 2);

        Livewire::test(ListAnnouncements::class)
            ->searchTable((string) $announcements->last()->id)
            ->assertCanSeeTableRecords([$announcements->last()])
            ->assertCanNotSeeTableRecords([$announcements->first()]);
    }

    #[Test]
    public function itFiltersToLiveAnnouncements(): void
    {
        $live = $this->create(Announcement::class, ['live' => true]);
        $notLive = $this->create(Announcement::class, ['live' => false]);

        Livewire::test(ListAnnouncements::class)
            ->filterTable('live', true)
            ->assertCanSeeTableRecords([$live])
            ->assertCanNotSeeTableRecords([$notLive]);
    }

    #[Test]
    public function itFiltersToAnnouncementsThatArentLive(): void
    {
        $live = $this->create(Announcement::class, ['live' => true]);
        $notLive = $this->create(Announcement::class, ['live' => false]);

        Livewire::test(ListAnnouncements::class)
            ->filterTable('live', false)
            ->assertCanSeeTableRecords([$notLive])
            ->assertCanNotSeeTableRecords([$live]);
    }

    #[Test]
    public function itFiltersToExpiredAnnouncements(): void
    {
        $current = $this->create(Announcement::class);
        $expired = $this->build(Announcement::class)->expired()->create();

        Livewire::test(ListAnnouncements::class)
            ->filterTable('expired', true)
            ->assertCanSeeTableRecords([$expired])
            ->assertCanNotSeeTableRecords([$current]);
    }

    #[Test]
    public function itFiltersToAnnouncementsThatHaventExpired(): void
    {
        $current = $this->create(Announcement::class);
        $expired = $this->build(Announcement::class)->expired()->create();

        Livewire::test(ListAnnouncements::class)
            ->filterTable('expired', false)
            ->assertCanSeeTableRecords([$current])
            ->assertCanNotSeeTableRecords([$expired]);
    }

    #[Test]
    public function itFlagsAnAnnouncementThatHasExpired(): void
    {
        $announcement = $this->build(Announcement::class)->expired()->create();

        Livewire::test(ListAnnouncements::class)->assertTableColumnStateSet('expired', true, $announcement);
    }

    #[Test]
    public function itDoesNotFlagAnAnnouncementThatIsStillCurrent(): void
    {
        $announcement = $this->create(Announcement::class);

        Livewire::test(ListAnnouncements::class)->assertTableColumnStateSet('expired', false, $announcement);
    }

    #[Test]
    public function itLinksEachRowToTheEditPage(): void
    {
        $announcement = $this->create(Announcement::class);

        $this->assertSame(
            AnnouncementResource::getUrl('edit', ['record' => $announcement]),
            Livewire::test(ListAnnouncements::class)->instance()->getTable()->getRecordUrl($announcement)
        );
    }

    #[Test]
    public function itOffersAnEditActionForEveryAnnouncement(): void
    {
        $announcement = $this->create(Announcement::class);

        Livewire::test(ListAnnouncements::class)->assertActionExists(TestAction::make(EditAction::class)->table($announcement));
    }

    #[Test]
    public function itDoesNotLetYouDeleteAnAnnouncement(): void
    {
        $announcement = $this->create(Announcement::class);

        Livewire::test(ListAnnouncements::class)->assertActionDoesNotExist(TestAction::make(DeleteAction::class)->table($announcement));
    }
}
