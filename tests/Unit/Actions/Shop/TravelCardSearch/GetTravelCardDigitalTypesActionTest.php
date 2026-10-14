<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\GetTravelCardDigitalTypesAction;
use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardPrice;
use App\Models\Shop\ShopDigitalCardType;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Support\Helpers;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Money\Money;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetTravelCardDigitalTypesActionTest extends TestCase
{
    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        ShopDigitalCardType::query()
            ->withoutGlobalScopes()
            ->get()
            ->each(fn (ShopDigitalCardType $type) => $type
                ->addMedia(UploadedFile::fake()->image('type.jpg'))
                ->toMediaCollection('primary'));

        /** @var ShopProduct $product */
        $product = $this->build(ShopProduct::class)
            ->has($this->build(ShopProductVariant::class), 'variants')
            ->create();

        $this->product = $product;
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

    /** @return Collection<int, array{title: string, link: string, image: string|null, products_count: int, price: string|null}> */
    protected function types(): Collection
    {
        return $this->callAction(GetTravelCardDigitalTypesAction::class);
    }

    #[Test]
    public function itOnlyReturnsLiveTypes(): void
    {
        $live = ShopDigitalCardType::query()->pluck('title');

        $this->assertEquals($live->all(), $this->types()->pluck('title')->all());
    }

    #[Test]
    public function itReturnsTheRouteForTheType(): void
    {
        $this->assertEquals(
            DigitalCardType::STANDARD->route(),
            $this->types()->firstWhere('title', DigitalCardType::STANDARD->name())['link'],
        );
    }

    #[Test]
    public function itCountsTheLiveCardsOfTheType(): void
    {
        $this->card(['type_id' => DigitalCardType::STANDARD]);
        $this->card(['type_id' => DigitalCardType::STANDARD]);
        $this->card(['type_id' => DigitalCardType::STANDARD, 'live' => false]);

        $this->assertSame(2, $this->types()->first()['products_count']);
    }

    #[Test]
    public function itReturnsTheCheapestCardPriceOfTheType(): void
    {
        $cheaper = $this->card(['type_id' => DigitalCardType::STANDARD]);
        $dearer = $this->card(['type_id' => DigitalCardType::STANDARD]);

        $this->build(ShopDigitalCardPrice::class)->forCard($cheaper)->create(['price' => 150]);
        $this->build(ShopDigitalCardPrice::class)->forCard($dearer)->create(['price' => 400]);

        $this->assertEquals('from £1.50', $this->types()->first()['price']);
    }

    #[Test]
    public function itFallsBackToTheDefaultPriceForCardsWithoutOneOfTheirOwn(): void
    {
        $this->card(['type_id' => DigitalCardType::STANDARD]);

        $this->assertEquals(
            'from ' . Helpers::formatMoney(Money::GBP(ShopDigitalCard::defaultPrice()->price)),
            $this->types()->first()['price'],
        );
    }

    #[Test]
    public function itReturnsNoPriceWhenTheTypeHasNoCards(): void
    {
        $this->assertNull($this->types()->first()['price']);
    }
}
