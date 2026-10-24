<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\GetOrderProductsForReviewAction;
use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardType;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetOrderProductsForReviewActionTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected ShopDigitalCardType $cardType;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        /** @var ShopOrder $order */
        $order = $this->create(ShopOrder::class);

        /** @var ShopProduct $product */
        $product = $this->build(ShopProduct::class)
            ->has($this->build(ShopProductVariant::class), 'variants')
            ->create();

        /** @var ShopProductVariant $variant */
        $variant = $product->variants->first();

        /** @var ShopDigitalCardType $cardType */
        $cardType = ShopDigitalCardType::query()->findOrFail(DigitalCardType::STANDARD->value);
        $cardType->addMedia(UploadedFile::fake()->image('card-type.jpg'))->toMediaCollection('primary');

        $this->order = $order;
        $this->product = $product;
        $this->variant = $variant;
        $this->cardType = $cardType;
    }

    /** @return Collection<int, array{id: int, title: string, variants: array<int, string>, image: string|null, link: string|null, type: string}> */
    protected function products(): Collection
    {
        return $this->callAction(GetOrderProductsForReviewAction::class, $this->order->unsetRelations());
    }

    protected function item(?ShopProduct $product = null, ?ShopProductVariant $variant = null): ShopOrderItem
    {
        $product ??= $this->product;

        /** @var ShopOrderItem $item */
        $item = $this->build(ShopOrderItem::class)
            ->inOrder($this->order)
            ->inProduct($product)
            ->inVariant($variant ?? $product->variants->first())
            ->create();

        return $item;
    }

    protected function card(array $attributes = []): ShopDigitalCard
    {
        /** @var ShopDigitalCard $card */
        $card = $this->build(ShopDigitalCard::class)
            ->forProduct($this->product)
            ->state($attributes)
            ->create();

        return $card;
    }

    protected function digitalItem(?ShopDigitalCard $card = null): ShopOrderDigitalItem
    {
        /** @var ShopOrderDigitalItem $item */
        $item = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($this->order)
            ->forCard($card ?? $this->card())
            ->create();

        return $item;
    }

    protected function secondProduct(): ShopProduct
    {
        /** @var ShopProduct $product */
        $product = $this->build(ShopProduct::class)
            ->has($this->build(ShopProductVariant::class), 'variants')
            ->create();

        return $product;
    }

    protected function discontinue(ShopProduct $product): void
    {
        ShopProductVariant::query()
            ->withoutGlobalScopes()
            ->where('product_id', $product->id)
            ->update(['live' => false]);
    }

    protected function hide(ShopDigitalCard $card): void
    {
        ShopDigitalCard::query()
            ->withoutGlobalScopes()
            ->whereKey($card->id)
            ->update(['live' => false]);
    }

    #[Test]
    public function itReturnsAnEmptyCollectionForAnOrderWithoutAnyItems(): void
    {
        $this->assertCount(0, $this->products());
    }

    #[Test]
    public function itReturnsAnEntryForEachPhysicalProductInTheOrder(): void
    {
        $secondProduct = $this->secondProduct();

        $this->item();
        $this->item($secondProduct);

        $products = $this->products();

        $this->assertCount(2, $products);
        $this->assertEquals([$this->product->id, $secondProduct->id], $products->pluck('id')->all());
        $this->assertEquals(['physical', 'physical'], $products->pluck('type')->all());
    }

    #[Test]
    public function itReturnsAListRatherThanAProductIdKeyedCollection(): void
    {
        $this->item($this->secondProduct());
        $this->item();

        $this->assertEquals([0, 1], $this->products()->keys()->all());
    }

    #[Test]
    public function itOnlyReturnsAProductOnceWhenTheOrderContainsMultipleVariantsOfIt(): void
    {
        /** @var ShopProductVariant $secondVariant */
        $secondVariant = $this->build(ShopProductVariant::class)->create(['product_id' => $this->product->id]);

        $this->item();
        $this->item($this->product, $secondVariant);

        $products = $this->products();

        $this->assertCount(1, $products);
        $this->assertEquals([$this->variant->title, $secondVariant->title], $products->first()['variants']);
    }

    #[Test]
    public function itOnlyReturnsAVariantTitleOnceWhenTheOrderContainsItMoreThanOnce(): void
    {
        $this->item();
        $this->item();

        $this->assertEquals([$this->variant->title], $this->products()->first()['variants']);
    }

    #[Test]
    public function itSkipsVariantsThatNoLongerExist(): void
    {
        $this->item();

        ShopProductVariant::query()->withoutGlobalScopes()->whereKey($this->variant->id)->delete();

        $this->assertEquals([], $this->products()->first()['variants']);
    }

    #[Test]
    public function itReturnsTheLiveProductTitleRatherThanTheOrderedTitle(): void
    {
        $this->item()->update(['product_title' => 'A stale variant specific title']);

        $this->assertEquals($this->product->title, $this->products()->first()['title']);
    }

    #[Test]
    public function itReturnsAVariantTitleThatIsNoLongerLive(): void
    {
        $this->item();

        $this->discontinue($this->product);

        $this->assertEquals([$this->variant->title], $this->products()->first()['variants']);
    }

    #[Test]
    public function itLinksToAProductThatIsStillSold(): void
    {
        $this->item();

        $this->assertEquals($this->product->link, $this->products()->first()['link']);
    }

    #[Test]
    public function itStillReturnsAProductThatIsNoLongerSoldButWithoutALinkToIt(): void
    {
        $this->item();

        $this->discontinue($this->product);

        $products = $this->products();

        $this->assertCount(1, $products);
        $this->assertEquals($this->product->id, $products->first()['id']);
        $this->assertNull($products->first()['link']);
    }

    #[Test]
    public function itReturnsTheWebpPrimaryImageForAPhysicalProduct(): void
    {
        $this->product->addMedia(UploadedFile::fake()->image('product.jpg'))->toMediaCollection('primary');

        $this->item();

        $this->assertEquals($this->product->main_image_as_webp, $this->products()->first()['image']);
    }

    #[Test]
    public function itFallsBackToTheFirstImageWhenAPhysicalProductHasNoPrimaryImage(): void
    {
        $this->product->addMedia(UploadedFile::fake()->image('additional.jpg'))->toMediaCollection('additional');

        $this->item();

        $this->assertEquals($this->product->first_image, $this->products()->first()['image']);
    }

    #[Test]
    public function itReturnsNoImageWhenAPhysicalProductHasNoMedia(): void
    {
        $this->item();

        $this->assertNull($this->products()->first()['image']);
    }

    #[Test]
    public function itReturnsAnEntryForEachDigitalItemInTheOrder(): void
    {
        $card = $this->card();

        $this->digitalItem($card);

        $products = $this->products();

        $this->assertCount(1, $products);
        $this->assertEquals([
            'id' => $card->id,
            'title' => $card->title,
            'variants' => [],
            'image' => $this->cardType->main_image_as_webp,
            'link' => DigitalCardType::STANDARD->route(),
            'type' => 'digital',
        ], $products->first());
    }

    #[Test]
    public function itReturnsEachDigitalItemSeparatelyRatherThanGroupingThemByCard(): void
    {
        $card = $this->card();

        $this->digitalItem($card);
        $this->digitalItem($card);

        $products = $this->products();

        $this->assertCount(2, $products);
        $this->assertEquals([$card->id, $card->id], $products->pluck('id')->all());
    }

    #[Test]
    public function itReturnsTheLiveCardTitleRatherThanTheOrderedTitle(): void
    {
        $card = $this->card();

        $this->digitalItem($card)->update(['digital_card_title' => 'A stale card title']);

        $this->assertEquals($card->title, $this->products()->first()['title']);
    }

    #[Test]
    public function itStillReturnsACardThatIsNoLongerLiveButWithoutALinkToIt(): void
    {
        $card = $this->card();

        $this->digitalItem($card);

        $this->hide($card);

        $products = $this->products();

        $this->assertCount(1, $products);
        $this->assertEquals($card->id, $products->first()['id']);
        $this->assertNull($products->first()['link']);
    }

    #[Test]
    public function itReturnsThePhysicalProductsBeforeTheDigitalCards(): void
    {
        $card = $this->card();

        $this->digitalItem($card);
        $this->item();

        $products = $this->products();

        $this->assertCount(2, $products);
        $this->assertEquals([$this->product->id, $card->id], $products->pluck('id')->all());
        $this->assertEquals(['physical', 'digital'], $products->pluck('type')->all());
    }
}
