<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\AskSealiac\AskSealiacChats\Tables;

use App\Filament\Resources\AskSealiac\AskSealiacChats\Pages\ListAskSealiacChats;
use App\Models\AskSealiacChat;
use App\Models\AskSealiacChatMessage;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AskSealiacChatsTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    /** The greeting only filter is on by default, so a chat needs more than one message to be listed. */
    protected function chatWithMessages(int $messages = 2, array $attributes = []): AskSealiacChat
    {
        $chat = $this->create(AskSealiacChat::class, $attributes);

        if ($messages > 0) {
            $this->build(AskSealiacChatMessage::class)->count($messages)->create(['ask_sealiac_chat_id' => $chat->id]);
        }

        return $chat;
    }

    #[Test]
    #[DataProvider('columns')]
    public function itShowsTheAskSealiacChatColumns(string $column): void
    {
        Livewire::test(ListAskSealiacChats::class)->assertTableColumnExists($column);
    }

    public static function columns(): array
    {
        return [
            'chat id' => ['chat_id'],
            'messages count' => ['messages_count'],
            'summary' => ['summary'],
            'created at' => ['created_at'],
        ];
    }

    #[Test]
    public function itDoesNotShowAnIdColumn(): void
    {
        Livewire::test(ListAskSealiacChats::class)->assertTableColumnDoesNotExist('id');
    }

    #[Test]
    public function itDoesNotShowASessionIdColumn(): void
    {
        Livewire::test(ListAskSealiacChats::class)->assertTableColumnDoesNotExist('session_id');
    }

    #[Test]
    public function itShowsTheNewestChatsFirst(): void
    {
        $chats = collect(range(1, 3))->map(fn (): AskSealiacChat => $this->chatWithMessages());

        Livewire::test(ListAskSealiacChats::class)
            ->assertCanSeeTableRecords($chats->reverse()->values(), inOrder: true);
    }

    #[Test]
    public function itCountsTheMessagesInAChat(): void
    {
        $chat = $this->chatWithMessages(4);

        Livewire::test(ListAskSealiacChats::class)
            ->assertTableColumnStateSet('messages_count', 4, $chat);
    }

    #[Test]
    public function itShowsTheChatSummary(): void
    {
        $chat = $this->chatWithMessages(attributes: ['summary' => 'The visitor asked where to eat in Crewe.']);

        Livewire::test(ListAskSealiacChats::class)
            ->assertTableColumnStateSet('summary', 'The visitor asked where to eat in Crewe.', $chat);
    }

    #[Test]
    public function itMarksAChatThatHasNotBeenSummarisedYet(): void
    {
        $chat = $this->chatWithMessages(attributes: ['summary' => null]);

        Livewire::test(ListAskSealiacChats::class)
            ->assertTableColumnStateSet('summary', null, $chat)
            ->assertSee('Not yet summarised');
    }

    #[Test]
    public function itSearchesByChatId(): void
    {
        $wanted = $this->chatWithMessages(attributes: ['chat_id' => 'a1b2c3d4']);
        $other = $this->chatWithMessages(attributes: ['chat_id' => 'e5f6a7b8']);

        Livewire::test(ListAskSealiacChats::class)
            ->searchTable('a1b2c3d4')
            ->assertCanSeeTableRecords([$wanted])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itSearchesBySessionId(): void
    {
        $wanted = $this->chatWithMessages(attributes: ['session_id' => 'BqZmJvXtLwPnRkGdHsYcFaEuTiOxNwVjMbKlQrZp']);
        $other = $this->chatWithMessages();

        Livewire::test(ListAskSealiacChats::class)
            ->searchTable('BqZmJvXtLwPnRkGdHsYcFaEuTiOxNwVjMbKlQrZp')
            ->assertCanSeeTableRecords([$wanted])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itSearchesById(): void
    {
        $chat = $this->chatWithMessages();

        Livewire::test(ListAskSealiacChats::class)
            ->searchTable((string) $chat->id)
            ->assertCanSeeTableRecords([$chat]);
    }

    #[Test]
    public function itHidesGreetingOnlyChatsByDefault(): void
    {
        $greetingOnly = $this->chatWithMessages(1);
        $answered = $this->chatWithMessages(2);

        Livewire::test(ListAskSealiacChats::class)
            ->assertCanSeeTableRecords([$answered])
            ->assertCanNotSeeTableRecords([$greetingOnly]);
    }

    #[Test]
    public function itHidesChatsWithNoMessagesAtAllByDefault(): void
    {
        $empty = $this->chatWithMessages(0);
        $answered = $this->chatWithMessages(2);

        Livewire::test(ListAskSealiacChats::class)
            ->assertCanSeeTableRecords([$answered])
            ->assertCanNotSeeTableRecords([$empty]);
    }

    #[Test]
    public function itShowsGreetingOnlyChatsWhenTheFilterIsTurnedOff(): void
    {
        $greetingOnly = $this->chatWithMessages(1);
        $answered = $this->chatWithMessages(2);

        Livewire::test(ListAskSealiacChats::class)
            ->filterTable('greetingOnly', false)
            ->assertCanSeeTableRecords([$answered, $greetingOnly]);
    }
}
