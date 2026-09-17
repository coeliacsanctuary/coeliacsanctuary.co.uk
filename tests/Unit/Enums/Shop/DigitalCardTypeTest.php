<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shop;

use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DigitalCardTypeTest extends TestCase
{
    protected function category(int $id): ShopCategory
    {
        /** @var ShopCategory $category */
        $category = $this->create(ShopCategory::class, ['id' => $id]);

        $category->products()->attach(
            $this->build(ShopProduct::class)
                ->has($this->build(ShopProductVariant::class), 'variants')
                ->create()
        );

        return $category;
    }

    #[Test]
    public function itReturnsTheStandardTypeForTheStandardCategorySlug(): void
    {
        $this->assertSame(
            DigitalCardType::STANDARD,
            DigitalCardType::fromRequestPath('digital-coeliac-gluten-free-travel-cards'),
        );
    }

    #[Test]
    public function itIgnoresTheShopPrefixOnTheRequestPath(): void
    {
        $this->assertSame(
            DigitalCardType::STANDARD,
            DigitalCardType::fromRequestPath('shop/digital-coeliac-gluten-free-travel-cards'),
        );
    }

    #[Test]
    public function itThrowsAnExceptionForAnUnknownCategorySlug(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid category');

        DigitalCardType::fromRequestPath('shop/some-other-category');
    }

    #[Test]
    public function itReturnsTheLabelForEachType(): void
    {
        $this->assertSame('Coeliac Gluten Free Travel Card', DigitalCardType::STANDARD->label());
        $this->assertSame('Coeliac+ Other Allergen Travel Card', DigitalCardType::PLUS->label());
    }

    #[Test]
    public function itPluralisesTheLabelForEachType(): void
    {
        $this->assertSame('Coeliac Gluten Free Travel Cards', DigitalCardType::STANDARD->pluralLabel());
        $this->assertSame('Coeliac+ Other Allergen Travel Cards', DigitalCardType::PLUS->pluralLabel());
    }

    #[Test]
    public function itPrefixesThePluralLabelWithDigitalForThePageTitle(): void
    {
        $this->assertSame('Digital Coeliac Gluten Free Travel Cards', DigitalCardType::STANDARD->pageTitle());
        $this->assertSame('Digital Coeliac+ Other Allergen Travel Cards', DigitalCardType::PLUS->pageTitle());
    }

    #[Test]
    public function itReturnsTheCategoryIdForEachType(): void
    {
        $this->assertSame(1, DigitalCardType::STANDARD->categoryId());
        $this->assertSame(11, DigitalCardType::PLUS->categoryId());
    }

    #[Test]
    public function itReturnsTheCategoryForTheStandardType(): void
    {
        $category = $this->category(1);

        $this->assertTrue(DigitalCardType::STANDARD->category()->is($category));
    }

    #[Test]
    public function itReturnsTheCategoryForThePlusType(): void
    {
        $category = $this->category(11);

        $this->assertTrue(DigitalCardType::PLUS->category()->is($category));
    }

    #[Test]
    public function itThrowsAnExceptionWhenTheCategoryDoesntExist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        DigitalCardType::STANDARD->category();
    }
}
