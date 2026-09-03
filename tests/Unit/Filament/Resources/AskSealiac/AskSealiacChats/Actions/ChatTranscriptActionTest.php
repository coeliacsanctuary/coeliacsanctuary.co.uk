<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\AskSealiac\AskSealiacChats\Actions;

use App\Filament\Resources\AskSealiac\AskSealiacChats\Pages\ListAskSealiacChats;
use App\Models\AskSealiacChat;
use App\Models\AskSealiacChatMessage;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChatTranscriptActionTest extends TestCase
{
    protected AskSealiacChat $chat;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));

        $this->chat = $this->create(AskSealiacChat::class);
    }

    protected function message(array $attributes = []): AskSealiacChatMessage
    {
        return $this->create(AskSealiacChatMessage::class, [
            'ask_sealiac_chat_id' => $this->chat->id,
            ...$attributes,
        ]);
    }

    protected function transcript(): TestAction
    {
        return TestAction::make('transcript')->table($this->chat);
    }

    /** The greeting only filter is on by default, and these chats deliberately have a single message. */
    protected function listPage(): Testable
    {
        return Livewire::test(ListAskSealiacChats::class)->filterTable('greetingOnly', false);
    }

    protected function transcriptHtml(): string
    {
        return $this->listPage()
            ->mountAction($this->transcript())
            ->instance()
            ->getMountedAction()
            ->getModalContent()
            ->render();
    }

    #[Test]
    public function itIsLabelledAndStyledAsASecondaryViewButton(): void
    {
        $this->message();

        $this->listPage()
            ->assertActionHasLabel($this->transcript(), 'View Chat')
            ->assertActionHasIcon($this->transcript(), Heroicon::Eye)
            ->assertActionHasColor($this->transcript(), 'gray');
    }

    #[Test]
    public function itOpensTheTranscriptInAReadOnlyModal(): void
    {
        $this->message();

        $this->listPage()->assertActionExists(
            $this->transcript(),
            fn (Action $action): bool => $action->getModalHeading() === 'Chat Transcript'
                && $action->getModalSubmitAction() === null,
        );
    }

    #[Test]
    public function itShowsTheChatSummaryAndSessionId(): void
    {
        $this->chat->update(['summary' => 'The visitor asked about gluten free pubs.']);

        $this->message();

        $html = $this->transcriptHtml();

        $this->assertStringContainsString('The visitor asked about gluten free pubs.', $html);
        $this->assertStringContainsString($this->chat->session_id, $html);
    }

    #[Test]
    public function itMarksAChatThatHasNotBeenSummarisedYet(): void
    {
        $this->chat->update(['summary' => null]);

        $this->message();

        $this->assertStringContainsString('Not yet summarised', $this->transcriptHtml());
    }

    #[Test]
    public function itShowsTheOldestMessagesFirst(): void
    {
        $this->message(['prompt' => 'First question', 'created_at' => now()->subHour()]);
        $this->message(['prompt' => 'Second question', 'created_at' => now()]);

        $this->assertMatchesRegularExpression('/First question.*Second question/s', $this->transcriptHtml());
    }

    #[Test]
    public function itRendersTheResponseAsMarkdown(): void
    {
        $this->message(['response' => "Here are your options:\n\n- The Gluten Free Kitchen\n- Coeliac Cafe"]);

        $this->assertStringContainsString('<li>The Gluten Free Kitchen</li>', $this->transcriptHtml());
    }

    #[Test]
    public function itEscapesHtmlEmbeddedInTheResponse(): void
    {
        $this->message(['response' => 'Try <script>alert(1)</script> instead']);

        $html = $this->transcriptHtml();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    #[Test]
    public function itDoesNotRenderThePromptAsMarkdown(): void
    {
        $this->message(['prompt' => 'Is *this* gluten free?']);

        $html = $this->transcriptHtml();

        $this->assertStringContainsString('Is *this* gluten free?', $html);
        $this->assertStringNotContainsString('<em>this</em>', $html);
    }

    #[Test]
    public function itShowsTheToolsUsedToAnswerAMessage(): void
    {
        $this->message([
            'tool_uses' => [
                ['tool' => 'SearchEateriesBySearchTermTool', 'data' => ['term' => 'crewe']],
            ],
        ]);

        $html = $this->transcriptHtml();

        $this->assertStringContainsString('SearchEateriesBySearchTermTool', $html);
        $this->assertStringContainsString('crewe', $html);
    }

    #[Test]
    public function itDoesNotShowAToolSectionWhenNoToolsWereUsed(): void
    {
        $this->message(['tool_uses' => []]);

        $this->assertStringNotContainsString('<details', $this->transcriptHtml());
    }
}
