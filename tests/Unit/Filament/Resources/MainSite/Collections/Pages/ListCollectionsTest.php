<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Collections\Pages;

use App\Filament\Resources\MainSite\Collections\Pages\ListCollections;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListCollectionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itLoadsTheCollectionList(): void
    {
        Livewire::test(ListCollections::class)->assertOk();
    }

    #[Test]
    public function itOffersAButtonToCreateACollection(): void
    {
        Livewire::test(ListCollections::class)->assertActionExists(CreateAction::class);
    }
}
