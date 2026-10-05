<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardType;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopDigitalCardTypeTest extends TestCase
{
    protected function standardType(): ShopDigitalCardType
    {
        /** @var ShopDigitalCardType $type */
        $type = ShopDigitalCardType::query()->findOrFail(DigitalCardType::STANDARD->value);

        return $type;
    }

    #[Test]
    public function itHasALiveScope(): void
    {
        $this->assertEquals(
            [DigitalCardType::STANDARD->value],
            ShopDigitalCardType::query()->pluck('id')->all(),
        );

        $this->assertCount(2, ShopDigitalCardType::query()->withoutGlobalScopes()->get());
    }

    #[Test]
    public function itCastsLiveToABoolean(): void
    {
        $this->assertIsBool($this->standardType()->live);
    }

    #[Test]
    public function itResolvesTheTypeFromTheRequestPath(): void
    {
        $this->assertTrue(
            ShopDigitalCardType::fromRequestPath('shop/digital-coeliac-gluten-free-travel-cards')
                ->is($this->standardType()),
        );
    }

    #[Test]
    public function itResolvesTheTypeFromARequestPathWithoutTheShopPrefix(): void
    {
        $this->assertTrue(
            ShopDigitalCardType::fromRequestPath('digital-coeliac-gluten-free-travel-cards')
                ->is($this->standardType()),
        );
    }

    #[Test]
    public function itThrowsAnExceptionForAnUnknownRequestPath(): void
    {
        $this->expectException(ModelNotFoundException::class);

        ShopDigitalCardType::fromRequestPath('shop/some-other-category');
    }

    #[Test]
    public function itThrowsAnExceptionWhenResolvingATypeThatIsntLive(): void
    {
        $this->expectException(ModelNotFoundException::class);

        ShopDigitalCardType::fromRequestPath('shop/digital-coeliac-plus-other-allergen-travel-cards');
    }

    #[Test]
    public function itHasManyCards(): void
    {
        $card = $this->create(ShopDigitalCard::class);

        $this->assertTrue($this->standardType()->cards->first()->is($card));
    }

    #[Test]
    public function itBelongsToAPhysicalCategory(): void
    {
        /** @var ShopCategory $category */
        $category = $this->create(ShopCategory::class, ['id' => 1]);

        $category->products()->attach(
            $this->build(ShopProduct::class)
                ->has($this->build(ShopProductVariant::class), 'variants')
                ->create()
        );

        $this->assertTrue($this->standardType()->category->is($category));
    }
}
