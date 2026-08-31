<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Schemas\Components;

use App\Filament\Schemas\Components\FaqsSection;
use App\Models\Recipes\Recipe;
use Closure;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\BuildsFilamentSchemas;
use Tests\TestCase;

class FaqsSectionTest extends TestCase
{
    use BuildsFilamentSchemas;

    protected Closure $undoRepeaterFake;

    protected Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->undoRepeaterFake = Repeater::fake();

        $this->recipe = $this->create(Recipe::class);
    }

    protected function tearDown(): void
    {
        ($this->undoRepeaterFake)();

        parent::tearDown();
    }

    #[Test]
    public function itAddsAFaqRepeater(): void
    {
        $this->assertInstanceOf(Repeater::class, $this->mountedComponent('faqs', [FaqsSection::make()], 'create', $this->recipe));
    }

    #[Test]
    public function itWritesToTheFaqsRelationship(): void
    {
        $this->assertSame('faqs', $this->mountedComponent('faqs', [FaqsSection::make()], 'create', $this->recipe)->getRelationshipName());
    }

    #[Test]
    public function itOrdersTheFaqsByPosition(): void
    {
        $this->assertSame('position', $this->mountedComponent('faqs', [FaqsSection::make()], 'create', $this->recipe)->getOrderColumn());
    }

    #[Test]
    public function itStartsWithNoRows(): void
    {
        $this->mountSchema([FaqsSection::make()], 'create', $this->recipe)->assertSchemaStateSet(['faqs' => []]);
    }

    #[Test]
    public function itRequiresAQuestionAndAnAnswerOnEachRow(): void
    {
        $repeater = $this->mountedComponent('faqs', [FaqsSection::make()], 'create', $this->recipe);

        $fields = collect($repeater->getDefaultChildComponents())
            ->mapWithKeys(fn ($field): array => [$field->getName() => $field]);

        $this->assertInstanceOf(TextInput::class, $fields['question']);
        $this->assertInstanceOf(Textarea::class, $fields['answer']);
        $this->assertTrue($fields['question']->isRequired());
        $this->assertTrue($fields['answer']->isRequired());
    }

    #[Test]
    public function itLabelsTheAddButton(): void
    {
        $this->assertSame('Add FAQ', $this->mountedComponent('faqs', [FaqsSection::make()], 'create', $this->recipe)->getAddActionLabel());
    }

    #[Test]
    public function itHasNoDisplayPositionByDefault(): void
    {
        $this->mountSchema([FaqsSection::make()], 'create', $this->recipe)->assertSchemaComponentDoesNotExist('faq_display');
    }

    #[Test]
    public function itCanAddADisplayPosition(): void
    {
        $field = $this->mountedComponent('faq_display', [FaqsSection::make(display: true)], 'create', $this->recipe);

        $this->assertInstanceOf(Select::class, $field);
        $this->assertSame(['top' => 'Above content', 'bottom' => 'Below content'], $field->getOptions());
    }

    #[Test]
    public function itIsCollapsible(): void
    {
        $this->assertTrue($this->mountedRootComponent([FaqsSection::make()], 'create', $this->recipe)->isCollapsible());
    }

    #[Test]
    public function itIsExpandedWhenCreating(): void
    {
        $this->assertFalse($this->mountedRootComponent([FaqsSection::make()], 'create', $this->recipe)->isCollapsed());
    }

    #[Test]
    public function itIsCollapsedWhenEditing(): void
    {
        $this->assertTrue($this->mountedRootComponent([FaqsSection::make()], 'edit', $this->recipe)->isCollapsed());
    }
}
