<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Popups\Pages;

use App\Filament\Resources\MainSite\Popups\Pages\ListPopups;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListPopupsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itLoadsThePopupList(): void
    {
        Livewire::test(ListPopups::class)->assertOk();
    }

    #[Test]
    public function itOffersAButtonToCreateAPopup(): void
    {
        Livewire::test(ListPopups::class)->assertActionExists(CreateAction::class);
    }
}
