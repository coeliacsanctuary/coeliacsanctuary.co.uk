<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shop;

use App\Enums\Shop\DigitalCardType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DigitalCardTypeTest extends TestCase
{
    #[Test]
    public function itReturnsTheNameForEachType(): void
    {
        $this->assertSame('Digital Coeliac Gluten Free Travel Cards', DigitalCardType::STANDARD->name());
        $this->assertSame('Digital Coeliac+ Other Allergen Travel Cards', DigitalCardType::PLUS->name());
    }

    #[Test]
    public function itReturnsTheRouteForTheStandardType(): void
    {
        $this->assertSame(route('shop.digital-standard-cards'), DigitalCardType::STANDARD->route());
    }

    #[Test]
    public function itThrowsAnExceptionForATypeWithoutARoute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid category');

        DigitalCardType::PLUS->route();
    }
}
