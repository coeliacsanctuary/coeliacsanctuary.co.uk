<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\AskSealiac\AskSealiacChats\AskSealiacChatResource;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AskSealiacChatPanelAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
    }

    #[Test]
    public function guestsAreSentToTheLoginPage(): void
    {
        $this->get(AskSealiacChatResource::getUrl('index'))->assertRedirect(Filament::getLoginUrl());
    }

    #[Test]
    public function signedInUsersCanOpenTheAskSealiacChatList(): void
    {
        $this->actingAs($this->create(User::class))
            ->get(AskSealiacChatResource::getUrl('index'))
            ->assertOk();
    }
}
