<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use App\Enums\Shop\DigitalCardColor;
use App\Enums\Shop\DigitalCardIcon;
use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopDigitalCardTest extends TestCase
{
    #[Test]
    public function itHasALiveScope(): void
    {
        $this->create(ShopDigitalCard::class, ['title' => 'French']);
        $this->build(ShopDigitalCard::class)->notLive()->create(['title' => 'German']);

        $this->assertEquals(['French'], ShopDigitalCard::query()->pluck('title')->all());
        $this->assertCount(2, ShopDigitalCard::query()->withoutGlobalScopes()->get());
    }

    #[Test]
    public function itCastsLiveToABoolean(): void
    {
        $card = $this->create(ShopDigitalCard::class);

        $this->assertIsBool($card->live);
    }

    #[Test]
    public function itCastsEnablePhysicalAddonsToABoolean(): void
    {
        $card = $this->create(ShopDigitalCard::class);

        $this->assertIsBool($card->enable_physical_addons);
    }

    #[Test]
    public function itCastsTheTypeToAnEnum(): void
    {
        $card = $this->create(ShopDigitalCard::class, ['type' => 'plus'])->fresh();

        $this->assertSame(DigitalCardType::PLUS, $card->type);
    }

    #[Test]
    public function itCastsTheColoursAndIconsToEnums(): void
    {
        $card = $this->create(ShopDigitalCard::class, [
            'cant_eat_colour' => 'red',
            'cant_eat_icon' => 'cross',
            'can_eat_colour' => 'green',
            'can_eat_icon' => 'tick',
        ])->fresh();

        $this->assertSame(DigitalCardColor::RED, $card->cant_eat_colour);
        $this->assertSame(DigitalCardIcon::CROSS, $card->cant_eat_icon);
        $this->assertSame(DigitalCardColor::GREEN, $card->can_eat_colour);
        $this->assertSame(DigitalCardIcon::TICK, $card->can_eat_icon);
    }

    #[Test]
    public function itHasAPrice(): void
    {
        $card = $this->create(ShopDigitalCard::class);

        $this->build(ShopDigitalCardPrice::class)->forCard($card)->create(['price' => 500]);

        $this->assertInstanceOf(ShopDigitalCardPrice::class, $card->refresh()->price);
        $this->assertEquals(500, $card->price->price);
    }

    #[Test]
    public function itDoesntHaveAPriceByDefault(): void
    {
        $card = $this->create(ShopDigitalCard::class);

        $this->assertNull($card->price);
    }

    #[Test]
    public function itBelongsToAProduct(): void
    {
        $product = $this->build(ShopProduct::class)
            ->has($this->build(ShopProductVariant::class), 'variants')
            ->create();

        $card = $this->build(ShopDigitalCard::class)->forProduct($product)->create();

        $this->assertInstanceOf(ShopProduct::class, $card->product);
        $this->assertTrue($card->product->is($product));
    }

    #[Test]
    public function itDoesntHaveToBelongToAProduct(): void
    {
        $card = $this->create(ShopDigitalCard::class);

        $this->assertNull($card->product);
    }

    #[Test]
    public function itCanGetTheDefaultPrice(): void
    {
        $card = $this->create(ShopDigitalCard::class);

        $this->build(ShopDigitalCardPrice::class)->forCard($card)->create(['price' => 500]);

        $this->assertEquals(0, ShopDigitalCard::defaultPrice()->digital_card_id);
        $this->assertEquals(100, ShopDigitalCard::defaultPrice()->price);
    }

    #[Test]
    public function itOnlyResolvesTheDefaultPriceOnce(): void
    {
        $this->assertSame(ShopDigitalCard::defaultPrice(), ShopDigitalCard::defaultPrice());
    }
}
