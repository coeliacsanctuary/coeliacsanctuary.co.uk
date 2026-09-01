<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Popups\Pages;

use App\Filament\Resources\MainSite\Popups\Pages\CreatePopup;
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

class CreatePopupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itCreatesThePopup(): void
    {
        $this->assertDatabaseEmpty(Popup::class);

        $this->createPopup()->assertNotified();

        $this->assertDatabaseCount(Popup::class, 1);

        $popup = $this->createdPopup();

        $this->assertSame('Visit the shop', $popup->text);
        $this->assertSame('/shop', $popup->link);
        $this->assertSame(7, $popup->display_every);
        $this->assertTrue($popup->live);
    }

    #[Test]
    public function itStoresThePrimaryImageInThePrimaryCollection(): void
    {
        $this->createPopup();

        $this->assertCount(1, $this->createdPopup()->getMedia('primary'));
    }

    #[Test]
    public function itStoresTheSecondaryImageInTheSecondaryCollection(): void
    {
        $this->createPopup();

        $popup = $this->createdPopup();

        $this->assertCount(1, $popup->getMedia('secondary'));
        $this->assertCount(1, $popup->getMedia('primary'));
    }

    #[Test]
    public function itCreatesAPopupWithoutASecondaryImage(): void
    {
        $this->createPopup(['secondary' => []]);

        $popup = $this->createdPopup();

        $this->assertCount(1, $popup->getMedia('primary'));
        $this->assertCount(0, $popup->getMedia('secondary'));
    }

    #[Test]
    public function itCreatesAPopupThatIsntLive(): void
    {
        $this->createPopup(['live' => false]);

        $this->assertFalse($this->createdPopup()->live);
    }

    #[Test]
    public function itSendsTheUserBackToThePopupListAfterCreating(): void
    {
        $this->createPopup()->assertRedirect(PopupResource::getUrl('index'));
    }

    protected function createPopup(array $overrides = []): Testable
    {
        return Livewire::test(CreatePopup::class)
            ->fillForm([
                'text' => 'Visit the shop',
                'link' => '/shop',
                'display_every' => 7,
                'live' => true,
                'primary' => [UploadedFile::fake()->image('primary.jpg')],
                'secondary' => [UploadedFile::fake()->image('secondary.jpg')],
                ...$overrides,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    protected function createdPopup(): Popup
    {
        return Popup::query()->withoutGlobalScopes()->firstOrFail();
    }
}
