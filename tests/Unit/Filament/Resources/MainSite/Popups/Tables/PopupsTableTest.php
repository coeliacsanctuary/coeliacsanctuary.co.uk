<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Popups\Tables;

use App\Filament\Resources\MainSite\Popups\Pages\ListPopups;
use App\Filament\Resources\MainSite\Popups\PopupResource;
use App\Models\Popup;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PopupsTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itShowsEveryPopupWhateverItsLiveState(): void
    {
        $live = $this->create(Popup::class, ['live' => true]);
        $notLive = $this->create(Popup::class, ['live' => false]);

        Livewire::test(ListPopups::class)->assertCanSeeTableRecords([$live, $notLive]);
    }

    #[Test]
    public function itShowsTheNewestPopupsFirst(): void
    {
        $popups = $this->create(Popup::class, 3);

        Livewire::test(ListPopups::class)
            ->assertCanSeeTableRecords($popups->reverse()->values(), inOrder: true);
    }

    #[Test]
    #[DataProvider('columns')]
    public function itShowsThePopupColumns(string $column): void
    {
        $this->create(Popup::class);

        Livewire::test(ListPopups::class)->assertTableColumnExists($column);
    }

    public static function columns(): array
    {
        return [
            'id' => ['id'],
            'text' => ['text'],
            'link' => ['link'],
            'display every' => ['display_every'],
            'live' => ['live'],
        ];
    }

    #[Test]
    public function itLabelsTheIdColumn(): void
    {
        Livewire::test(ListPopups::class)
            ->assertTableColumnExists('id', fn (TextColumn $column): bool => $column->getLabel() === 'ID');
    }

    #[Test]
    public function itDoesNotLetYouSearchThePopups(): void
    {
        $this->assertFalse(Livewire::test(ListPopups::class)->instance()->getTable()->isSearchable());
    }

    #[Test]
    public function itLinksEachRowToTheEditPage(): void
    {
        $popup = $this->create(Popup::class);

        $this->assertSame(
            PopupResource::getUrl('edit', ['record' => $popup]),
            Livewire::test(ListPopups::class)->instance()->getTable()->getRecordUrl($popup)
        );
    }

    #[Test]
    public function itOffersAnEditActionForEveryPopup(): void
    {
        $popup = $this->create(Popup::class);

        Livewire::test(ListPopups::class)->assertActionExists(TestAction::make(EditAction::class)->table($popup));
    }
}
