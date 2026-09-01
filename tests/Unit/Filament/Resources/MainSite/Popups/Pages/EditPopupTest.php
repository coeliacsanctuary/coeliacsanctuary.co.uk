<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Popups\Pages;

use App\Filament\Resources\MainSite\Popups\Pages\EditPopup;
use App\Filament\Resources\MainSite\Popups\PopupResource;
use App\Models\Popup;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditPopupTest extends TestCase
{
    protected Popup $popup;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->actingAs($this->create(User::class));

        $this->popup = $this->create(Popup::class, [
            'text' => 'Visit the shop',
            'link' => '/shop',
            'display_every' => 7,
            'live' => true,
        ]);

        $this->popup->addMedia(UploadedFile::fake()->image('primary.jpg'))->toMediaCollection('primary');
    }

    #[Test]
    public function itFillsTheFormFromThePopup(): void
    {
        $this->editPage()->assertSchemaStateSet([
            'text' => 'Visit the shop',
            'link' => '/shop',
            'display_every' => 7,
            'live' => true,
        ]);
    }

    #[Test]
    public function itUpdatesThePopup(): void
    {
        $this->editPage()
            ->fillForm(['text' => 'Visit the gluten free shop', 'display_every' => 14])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        $this->popup->refresh();

        $this->assertSame('Visit the gluten free shop', $this->popup->text);
        $this->assertSame(14, $this->popup->display_every);
    }

    #[Test]
    public function itAddsASecondaryImageToAPopupThatHasNone(): void
    {
        $this->assertCount(0, $this->popup->getMedia('secondary'));

        $this->editPage()
            ->fillForm(['secondary' => [UploadedFile::fake()->image('secondary.jpg')]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->popup->refresh();

        $this->assertCount(1, $this->popup->getMedia('secondary'));
        $this->assertCount(1, $this->popup->getMedia('primary'));
    }

    #[Test]
    public function itKeepsTheTwoImageCollectionsSeparate(): void
    {
        $this->popup->addMedia(UploadedFile::fake()->image('secondary.jpg'))->toMediaCollection('secondary');

        $this->editPage()->call('save')->assertHasNoFormErrors();

        $this->popup->refresh();

        $this->assertCount(1, $this->popup->getMedia('primary'));
        $this->assertCount(1, $this->popup->getMedia('secondary'));

        $this->assertSame('primary.jpg', $this->popup->getMedia('primary')->first()->file_name);
        $this->assertSame('secondary.jpg', $this->popup->getMedia('secondary')->first()->file_name);
    }

    #[Test]
    public function itTakesAPopupOffline(): void
    {
        $this->editPage()->fillForm(['live' => false])->call('save')->assertHasNoFormErrors();

        $this->assertFalse($this->popup->refresh()->live);
    }

    #[Test]
    public function itCanEditAPopupThatIsNotLive(): void
    {
        $popup = $this->create(Popup::class, ['live' => false]);

        Livewire::test(EditPopup::class, ['record' => $popup->getRouteKey()])->assertOk();
    }

    #[Test]
    public function itSendsTheUserBackToThePopupListAfterSaving(): void
    {
        $this->editPage()->call('save')->assertRedirect(PopupResource::getUrl('index'));
    }

    protected function editPage(): Testable
    {
        return Livewire::test(EditPopup::class, ['record' => $this->popup->getRouteKey()]);
    }
}
