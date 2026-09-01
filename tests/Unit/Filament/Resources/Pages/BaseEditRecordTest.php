<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\Pages;

use App\Filament\Resources\MainSite\Redirects\RedirectResource;
use App\Models\Redirect;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\Filament\EditRecordTestPage;
use Tests\TestCase;

class BaseEditRecordTest extends TestCase
{
    protected Redirect $record;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));

        $this->record = $this->create(Redirect::class, ['from' => 'old-page', 'to' => 'new-page']);
    }

    #[Test]
    public function itAddsASaveAndContinueEditingButtonToAnyEditPage(): void
    {
        $this->editPage()->assertSee('Save &amp; continue editing', escape: false);
    }

    #[Test]
    public function itSavesTheRecordWhenContinuingToEdit(): void
    {
        $this->editPage()
            ->fillForm(['to' => 'an-even-newer-page'])
            ->call('saveAndContinueEditing')
            ->assertNotified()
            ->assertHasNoFormErrors();

        $this->assertSame('an-even-newer-page', $this->record->refresh()->to);
    }

    #[Test]
    public function itReturnsToTheEditPageAfterSavingAndContinuing(): void
    {
        $this->editPage()
            ->call('saveAndContinueEditing')
            ->assertRedirect(RedirectResource::getUrl('edit', ['record' => $this->record]));
    }

    #[Test]
    public function itStillRedirectsToTheIndexWhenSavingNormally(): void
    {
        $this->editPage()
            ->call('save')
            ->assertRedirect(RedirectResource::getUrl('index'));
    }

    #[Test]
    public function itDoesNotSaveTheRecordWhenTheFormIsInvalid(): void
    {
        $this->editPage()
            ->fillForm(['to' => ''])
            ->call('saveAndContinueEditing')
            ->assertHasFormErrors(['to' => 'required'])
            ->assertNoRedirect();

        $this->assertSame('new-page', $this->record->refresh()->to);
    }

    protected function editPage(): Testable
    {
        return Livewire::test(EditRecordTestPage::class, ['record' => $this->record->getRouteKey()]);
    }
}
