<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Schemas\Components;

use App\Filament\Schemas\Components\MetasSection;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\BuildsFilamentSchemas;
use Tests\TestCase;

class MetasSectionTest extends TestCase
{
    use BuildsFilamentSchemas;

    #[Test]
    public function itAddsARequiredMetaTagsInput(): void
    {
        $field = $this->mountedComponent('meta_tags', [MetasSection::make()]);

        $this->assertInstanceOf(TextInput::class, $field);
        $this->assertTrue($field->isRequired());
    }

    #[Test]
    public function itAddsARequiredMetaDescriptionTextarea(): void
    {
        $field = $this->mountedComponent('meta_description', [MetasSection::make()]);

        $this->assertInstanceOf(Textarea::class, $field);
        $this->assertTrue($field->isRequired());
    }

    #[Test]
    public function itCanOmitTheMetaTagsInput(): void
    {
        $this->mountSchema([MetasSection::make(metaTags: false)])
            ->assertSchemaComponentDoesNotExist('meta_tags')
            ->assertSchemaComponentExists('meta_description');
    }

    #[Test]
    public function itCanOmitTheMetaDescriptionTextarea(): void
    {
        $this->mountSchema([MetasSection::make(metaDescription: false)])
            ->assertSchemaComponentDoesNotExist('meta_description')
            ->assertSchemaComponentExists('meta_tags');
    }

    #[Test]
    public function itCanRenameTheFields(): void
    {
        $this->mountSchema([MetasSection::make(metaTags: 'seo_tags', metaDescription: 'seo_description')])
            ->assertSchemaComponentExists('seo_tags')
            ->assertSchemaComponentExists('seo_description');
    }

    #[Test]
    public function itIsCollapsible(): void
    {
        $this->assertTrue($this->mountedRootComponent([MetasSection::make()])->isCollapsible());
    }

    #[Test]
    public function itIsExpandedWhenCreating(): void
    {
        $this->assertFalse($this->mountedRootComponent([MetasSection::make()], 'create')->isCollapsed());
    }

    #[Test]
    public function itIsCollapsedWhenEditing(): void
    {
        $this->assertTrue($this->mountedRootComponent([MetasSection::make()], 'edit')->isCollapsed());
    }
}
