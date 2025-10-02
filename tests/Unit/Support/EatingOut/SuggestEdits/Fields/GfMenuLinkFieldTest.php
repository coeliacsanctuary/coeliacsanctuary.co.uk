<?php

declare(strict_types=1);

namespace Tests\Unit\Support\EatingOut\SuggestEdits\Fields;

use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Support\EatingOut\SuggestEdits\Fields\GfMenuLinkField;
use Database\Seeders\EateryScaffoldingSeeder;
use Tests\TestCase;

class GfMenuLinkFieldTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itReturnsTheDatabaseValue(): void
    {
        $field = app(GfMenuLinkField::class);

        $this->assertEquals($this->eatery->gf_menu_link, $field->getCurrentValue($this->eatery));
    }

    #[Test]
    public function itReturnsThePreparedValue(): void
    {
        $field = GfMenuLinkField::make('Foo');

        $this->assertEquals('Foo', $field->prepare());
    }

    #[Test]
    public function itReturnsTheValueForDisplay(): void
    {
        $field = GfMenuLinkField::make('Foo');

        $this->assertEquals('Foo', $field->getSuggestedValue());
    }

    #[Test]
    public function itCanCommitTheSuggestedValue(): void
    {
        $field = GfMenuLinkField::make('Foo');

        $this->assertNotEquals('Foo', $this->eatery->gf_menu_link);

        $field->commitSuggestedValue($this->eatery);

        $this->assertEquals('Foo', $this->eatery->refresh()->gf_menu_link);
    }
}
