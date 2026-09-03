<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\AskSealiac\AskSealiacChats;

use App\Filament\Resources\AskSealiac\AskSealiacChats\AskSealiacChatResource;
use App\Models\AskSealiacChat;
use App\Models\AskSealiacChatMessage;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AskSealiacChatResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itRegistersOnlyTheListPage(): void
    {
        $this->assertSame(['index'], array_keys(AskSealiacChatResource::getPages()));
    }

    #[Test]
    public function itIsNotGloballySearchable(): void
    {
        $this->assertFalse(AskSealiacChatResource::canGloballySearch());
    }

    #[Test]
    public function itCountsTheMessagesOnEachChat(): void
    {
        $chat = $this->create(AskSealiacChat::class);

        $this->build(AskSealiacChatMessage::class)->count(3)->create(['ask_sealiac_chat_id' => $chat->id]);

        $this->assertSame(3, AskSealiacChatResource::getEloquentQuery()->first()->messages_count);
    }

    #[Test]
    public function itIsLabelledAsChatsInTheNavigation(): void
    {
        $this->assertSame('Chats', AskSealiacChatResource::getNavigationLabel());
    }

    #[Test]
    public function itIsLabelledAsChatsInHeadingsAndBreadcrumbs(): void
    {
        $this->assertSame('Chats', AskSealiacChatResource::getPluralModelLabel());
        $this->assertSame('Chat', AskSealiacChatResource::getModelLabel());
    }
}
