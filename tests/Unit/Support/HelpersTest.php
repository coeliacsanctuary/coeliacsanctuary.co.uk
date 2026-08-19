<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Models\Shop\ShopCategory;
use App\Models\User;
use App\Support\Helpers;
use Money\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    #[Test]
    public function itCanConvertMilesToMeters(): void
    {
        $miles = 5;
        $ratio = 1609.344;

        $this->assertEquals(round($miles * $ratio), Helpers::milesToMeters($miles));
    }

    #[Test]
    #[DataProvider('searchTermProvider')]
    public function itCanFormatASearchTerm(string $term, string $expected): void
    {
        $this->assertEquals($expected, Helpers::formatSearchTerm($term));
    }

    public static function searchTermProvider(): array
    {
        return [
            'lower case postcode' => ['cw1 2ab', 'CW1 2AB'],
            'mixed case postcode' => ['Sw1a 1aa', 'SW1A 1AA'],
            'postcode without a space' => ['cw12ab', 'CW12AB'],
            'single letter postcode area' => ['m1 1ae', 'M1 1AE'],
            'padded postcode' => ['  cw1 2ab  ', 'CW1 2AB'],
            'town' => ['crewe', 'Crewe'],
            'multi word town' => ['newcastle upon tyne', 'Newcastle Upon Tyne'],
            'county' => ['yorkshire', 'Yorkshire'],
            'phrase' => ['fish and chips', 'Fish and Chips'],
        ];
    }

    #[Test]
    public function itCanFormatMoney(): void
    {
        $amount = Money::GBP(1000);

        $this->assertEquals('£10.00', Helpers::formatMoney($amount));
    }

    #[Test]
    public function itCanReturnTheAdminUser(): void
    {
        $this->withAdminUser();

        $user = User::query()->firstWhere('email', 'contact@coeliacsanctuary.co.uk');

        $this->assertTrue(Helpers::adminUser()->is($user));
    }

    #[Test]
    #[DataProvider('travelCardCategoryIdProvider')]
    public function itCanDetermineIfACategoryIdIsATravelCardCategory(?int $categoryId, bool $expected): void
    {
        $this->assertEquals($expected, Helpers::isTravelCard($categoryId));
    }

    public static function travelCardCategoryIdProvider(): array
    {
        return [
            'standard coeliac travel cards' => [1, true],
            'coeliac plus other allergen cards' => [11, true],
            'another category' => [5, false],
            'null' => [null, false],
        ];
    }

    #[Test]
    public function itCanDetermineIfACategoryInstanceIsATravelCardCategory(): void
    {
        $this->assertTrue(Helpers::isTravelCard($this->create(ShopCategory::class, ['id' => 1])));
        $this->assertFalse(Helpers::isTravelCard($this->create(ShopCategory::class, ['id' => 5])));
    }
}
