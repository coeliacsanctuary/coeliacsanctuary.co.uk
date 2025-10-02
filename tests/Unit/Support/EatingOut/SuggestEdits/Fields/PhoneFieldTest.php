<?php

declare(strict_types=1);

namespace Tests\Unit\Support\EatingOut\SuggestEdits\Fields;

use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Support\EatingOut\SuggestEdits\Fields\PhoneField;
use Database\Seeders\EateryScaffoldingSeeder;
use Tests\TestCase;

class PhoneFieldTest extends TestCase
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
        $field = app(PhoneField::class);

        $this->assertEquals($this->eatery->phone, $field->getCurrentValue($this->eatery));
    }

    #[Test]
    public function itReturnsThePreparedValue(): void
    {
        $field = PhoneField::make('Foo');

        $this->assertEquals('Foo', $field->prepare());
    }

    #[Test]
    public function itReturnsTheValueForDisplay(): void
    {
        $field = PhoneField::make('Foo');

        $this->assertEquals('Foo', $field->getSuggestedValue());
    }

    #[Test]
    public function itCanCommitTheSuggestedValue(): void
    {
        $field = PhoneField::make('Foo');

        $this->assertNotEquals('Foo', $this->eatery->phone);

        $field->commitSuggestedValue($this->eatery);

        $this->assertEquals('Foo', $this->eatery->refresh()->phone);
    }
}
