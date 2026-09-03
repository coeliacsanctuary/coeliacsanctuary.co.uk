<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\AskSealiac\AskSealiacChats\Pages;

use App\Filament\Resources\AskSealiac\AskSealiacChats\Pages\ListAskSealiacChats;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListAskSealiacChatsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itLoadsTheAskSealiacChatList(): void
    {
        Livewire::test(ListAskSealiacChats::class)->assertOk();
    }

    #[Test]
    public function itDoesNotOfferAButtonToCreateAChat(): void
    {
        Livewire::test(ListAskSealiacChats::class)->assertActionDoesNotExist(CreateAction::class);
    }
}
