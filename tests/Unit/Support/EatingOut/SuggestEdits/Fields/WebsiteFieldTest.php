<?php

declare(strict_types=1);

namespace Tests\Unit\Support\EatingOut\SuggestEdits\Fields;

use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Support\EatingOut\SuggestEdits\Fields\WebsiteField;
use Database\Seeders\EateryScaffoldingSeeder;
use Tests\TestCase;

class WebsiteFieldTest extends TestCase
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
        $field = app(WebsiteField::class);

        $this->assertEquals($this->eatery->website, $field->getCurrentValue($this->eatery));
    }

    #[Test]
    public function itReturnsThePreparedValue(): void
    {
        $field = WebsiteField::make('Foo');

        $this->assertEquals('Foo', $field->prepare());
    }

    #[Test]
    public function itReturnsTheValueForDisplay(): void
    {
        $field = WebsiteField::make('Foo');

        $this->assertEquals('Foo', $field->getSuggestedValue());
    }

    #[Test]
    public function itCanCommitTheSuggestedValue(): void
    {
        $field = WebsiteField::make('Foo');

        $this->assertNotEquals('Foo', $this->eatery->website);

        $field->commitSuggestedValue($this->eatery);

        $this->assertEquals('Foo', $this->eatery->refresh()->website);
    }
}
