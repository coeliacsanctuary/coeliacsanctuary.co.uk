<?php

declare(strict_types=1);

namespace Tests\Unit\Support\EatingOut\SuggestEdits\Fields;

use App\Models\EatingOut\Eatery;
use App\Support\EatingOut\SuggestEdits\Fields\AddressField;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddressFieldTest extends TestCase
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
        $field = app(AddressField::class);

        $this->assertEquals($this->eatery->address, $field->getCurrentValue($this->eatery));
    }

    #[Test]
    public function itReturnsThePreparedValue(): void
    {
        $field = AddressField::make('Foo');

        $this->assertEquals('Foo', $field->prepare());
    }

    #[Test]
    public function itReturnsTheValueForDisplay(): void
    {
        $field = AddressField::make('Foo');

        $this->assertEquals('Foo', $field->getSuggestedValue());
    }

    #[Test]
    public function itCanCommitTheSuggestedValue(): void
    {
        $field = AddressField::make('Foo');

        $this->assertNotEquals('Foo', $this->eatery->address);

        $field->commitSuggestedValue($this->eatery);

        $this->assertEquals('Foo', $this->eatery->refresh()->address);
    }
}
