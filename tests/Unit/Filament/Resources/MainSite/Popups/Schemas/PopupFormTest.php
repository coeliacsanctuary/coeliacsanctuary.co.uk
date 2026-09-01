<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Popups\Schemas;

use App\Filament\Resources\MainSite\Popups\Pages\CreatePopup;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PopupFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itAcceptsACompletePopup(): void
    {
        $this->fillCreateForm()->call('create')->assertHasNoFormErrors();
    }

    #[Test]
    #[DataProvider('invalidPopups')]
    public function itValidatesThePopup(array $overrides, array $errors): void
    {
        $this->fillCreateForm($overrides)
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    }

    public static function invalidPopups(): array
    {
        return [
            '`text` is required' => [['text' => null], ['text' => 'required']],
            '`link` is required' => [['link' => null], ['link' => 'required']],
            '`link` is max 50 characters' => [['link' => '/' . Str::repeat('a', 50)], ['link' => 'max']],
            '`display_every` is required' => [['display_every' => null], ['display_every' => 'required']],
            '`display_every` must be a number' => [['display_every' => 'a week'], ['display_every' => 'numeric']],
            '`primary` is required' => [['primary' => []], ['primary' => 'required']],
        ];
    }

    #[Test]
    public function itAcceptsAPopupWithNoSecondaryImage(): void
    {
        $this->fillCreateForm(['secondary' => []])->call('create')->assertHasNoFormErrors();
    }

    #[Test]
    public function itOnlyAcceptsAnImageForThePrimaryImage(): void
    {
        $this->fillCreateForm(['primary' => [UploadedFile::fake()->create('popup.pdf', 10, 'application/pdf')]])
            ->call('create')
            ->assertHasFormErrors(['primary']);
    }

    #[Test]
    public function itOnlyAcceptsAnImageForTheSecondaryImage(): void
    {
        $this->fillCreateForm(['secondary' => [UploadedFile::fake()->create('popup.pdf', 10, 'application/pdf')]])
            ->call('create')
            ->assertHasFormErrors(['secondary']);
    }

    #[Test]
    public function itLabelsBothImageFields(): void
    {
        $form = Livewire::test(CreatePopup::class)->instance()->form;

        $this->assertSame('Primary Image', $this->imageField($form, 'primary')->getLabel());
        $this->assertSame('Secondary Image', $this->imageField($form, 'secondary')->getLabel());
    }

    #[Test]
    public function itExplainsWhatTheSecondaryImageIsFor(): void
    {
        Livewire::test(CreatePopup::class)->assertSee('eg Portrait images');
    }

    protected function imageField(mixed $form, string $name): SpatieMediaLibraryFileUpload
    {
        /** @var SpatieMediaLibraryFileUpload $field */
        $field = $form->getFlatComponents(withHidden: true)[$name];

        return $field;
    }

    protected function fillCreateForm(array $overrides = []): Testable
    {
        return Livewire::test(CreatePopup::class)->fillForm([
            'text' => 'Visit the shop',
            'link' => '/shop',
            'display_every' => 7,
            'live' => true,
            'primary' => [UploadedFile::fake()->image('primary.jpg')],
            'secondary' => [UploadedFile::fake()->image('secondary.jpg')],
            ...$overrides,
        ]);
    }
}
