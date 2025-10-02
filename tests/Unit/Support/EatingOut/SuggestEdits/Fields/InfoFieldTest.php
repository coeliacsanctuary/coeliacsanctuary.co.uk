<?php

declare(strict_types=1);

namespace Tests\Unit\Support\EatingOut\SuggestEdits\Fields;

use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Support\EatingOut\SuggestEdits\Fields\InfoField;
use Database\Seeders\EateryScaffoldingSeeder;
use RuntimeException;
use Tests\TestCase;

class InfoFieldTest extends TestCase
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
        $field = app(InfoField::class);

        $this->assertEquals('', $field->getCurrentValue($this->eatery));
    }

    #[Test]
    public function itReturnsThePreparedValue(): void
    {
        $field = InfoField::make('Foo');

        $this->assertEquals('Foo', $field->prepare());
    }

    #[Test]
    public function itReturnsTheValueForDisplay(): void
    {
        $field = InfoField::make('Foo');

        $this->assertEquals('Foo', $field->getSuggestedValue());
    }

    #[Test]
    public function itCanNotCommitTheSuggestedValue(): void
    {
        $field = InfoField::make('Foo');

        $this->expectException(RuntimeException::class);

        $field->commitSuggestedValue($this->eatery);
    }
}
