<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Pages;

use App\Actions\FetchAdsTxtAction;
use App\Filament\Pages\RefreshAdsTxt;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RefreshAdsTxtTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itLoadsThePage(): void
    {
        $this->page()->assertOk();
    }

    #[Test]
    public function itIsLabelledAsAReloadButton(): void
    {
        $this->page()->assertActionHasLabel($this->refreshAction(), 'Reload ads.txt from mediavine');
    }

    #[Test]
    public function itFetchesTheAdsTxtWhenTheButtonIsClicked(): void
    {
        $this->expectAction(FetchAdsTxtAction::class);

        $this->page()->callAction($this->refreshAction());
    }

    #[Test]
    public function itNotifiesOnceTheAdsTxtHasBeenRefreshed(): void
    {
        $this->expectAction(FetchAdsTxtAction::class);

        $this->page()
            ->callAction($this->refreshAction())
            ->assertNotified();
    }

    protected function refreshAction(): TestAction
    {
        return TestAction::make('refresh')->schemaComponent();
    }

    protected function page(): Testable
    {
        return Livewire::test(RefreshAdsTxt::class);
    }
}
