<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Announcements\Pages;

use App\Filament\Resources\MainSite\Announcements\Pages\ListAnnouncements;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListAnnouncementsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itLoadsTheAnnouncementList(): void
    {
        Livewire::test(ListAnnouncements::class)->assertOk();
    }

    #[Test]
    public function itOffersAButtonToCreateAnAnnouncement(): void
    {
        Livewire::test(ListAnnouncements::class)->assertActionExists(CreateAction::class);
    }
}
