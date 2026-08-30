<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Schemas\Components;

use App\Filament\Schemas\Components\ImagesSection;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\BuildsFilamentSchemas;
use Tests\TestCase;

class ImagesSectionTest extends TestCase
{
    use BuildsFilamentSchemas;

    #[Test]
    public function itAddsARequiredHeaderImageOnThePrimaryCollection(): void
    {
        $field = $this->mountedComponent('header', [ImagesSection::make()]);

        $this->assertInstanceOf(SpatieMediaLibraryFileUpload::class, $field);
        $this->assertTrue($field->isRequired());
        $this->assertSame('primary', $field->getCollection());
    }

    #[Test]
    public function itAddsARequiredSocialImageOnTheSocialCollection(): void
    {
        $field = $this->mountedComponent('social', [ImagesSection::make()]);

        $this->assertInstanceOf(SpatieMediaLibraryFileUpload::class, $field);
        $this->assertTrue($field->isRequired());
        $this->assertSame('social', $field->getCollection());
    }

    #[Test]
    public function theSocialImageIsRequiredWhenEditingTheSameAsWhenCreating(): void
    {
        $this->assertTrue($this->mountedComponent('social', [ImagesSection::make()], 'edit')->isRequired());
    }

    #[Test]
    public function itDoesNotOfferAToggleToReuseTheHeaderImage(): void
    {
        $this->mountSchema([ImagesSection::make()])
            ->assertSchemaComponentDoesNotExist('social_use_header_image');
    }

    #[Test]
    public function itHasNoSquareImageByDefault(): void
    {
        $this->mountSchema([ImagesSection::make()])->assertSchemaComponentDoesNotExist('square');
    }

    #[Test]
    public function itCanAddAnOptionalSquareImage(): void
    {
        $field = $this->mountedComponent('square', [ImagesSection::make(squareImage: true)]);

        $this->assertSame('square', $field->getCollection());
        $this->assertFalse($field->isRequired());
    }

    #[Test]
    public function itCanOmitTheHeaderImage(): void
    {
        $this->mountSchema([ImagesSection::make(headerImage: false)])
            ->assertSchemaComponentDoesNotExist('header')
            ->assertSchemaComponentExists('social');
    }

    #[Test]
    public function itCanOmitTheSocialImage(): void
    {
        $this->mountSchema([ImagesSection::make(socialImage: false)])
            ->assertSchemaComponentDoesNotExist('social')
            ->assertSchemaComponentExists('header');
    }

    #[Test]
    public function itHasNoHeaderImageAltTextByDefault(): void
    {
        $this->mountSchema([ImagesSection::make()])->assertSchemaComponentDoesNotExist('header_image_alt_text');
    }

    #[Test]
    public function itCanAddAnOptionalHeaderImageAltText(): void
    {
        $field = $this->mountedComponent('header_image_alt_text', [ImagesSection::make(headerImageAltText: true)]);

        $this->assertInstanceOf(TextInput::class, $field);
        $this->assertFalse($field->isRequired());
        $this->assertSame('Alt Text', $field->getLabel());
    }

    #[Test]
    public function itAppendsAdditionalImageSections(): void
    {
        $this->mountSchema([
            ImagesSection::make(additionalImages: fn (): Section => Section::make('Extra')->schema([
                SpatieMediaLibraryFileUpload::make('extra')->collection('extra'),
            ])),
        ])->assertSchemaComponentExists('extra');
    }

    #[Test]
    public function itIsCollapsible(): void
    {
        $this->assertTrue($this->mountedRootComponent([ImagesSection::make()])->isCollapsible());
    }

    #[Test]
    public function itIsExpandedWhenCreating(): void
    {
        $this->assertFalse($this->mountedRootComponent([ImagesSection::make()], 'create')->isCollapsed());
    }

    #[Test]
    public function itIsCollapsedWhenEditing(): void
    {
        $this->assertTrue($this->mountedRootComponent([ImagesSection::make()], 'edit')->isCollapsed());
    }
}
