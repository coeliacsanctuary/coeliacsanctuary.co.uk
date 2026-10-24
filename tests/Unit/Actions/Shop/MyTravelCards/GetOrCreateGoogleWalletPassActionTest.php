<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\MyTravelCards;

use App\Actions\Shop\MyTravelCards\GetOrCreateGoogleWalletPassAction;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Tests\TestCase;

class GetOrCreateGoogleWalletPassActionTest extends TestCase
{
    protected ShopCustomer $customer;

    protected ShopDigitalCard $card;

    protected ShopOrderDigitalItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        cache()->put('mobile-pass.google.access-token', 'test-token', 3600);

        config()->set('mobile-pass.google.issuer_id', '3388000000000000001');

        $this->customer = $this->create(ShopCustomer::class);

        /** @var ShopOrder $order */
        $order = $this->build(ShopOrder::class)->asPaid($this->customer)->create();

        $this->card = $this->build(ShopDigitalCard::class)->create([
            'title' => 'Spanish Travel Card',
            'slug' => 'spanish-travel-card',
            'card_title' => 'Card title in the native language',
        ]);

        /** @var ShopOrderDigitalItem $item */
        $item = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($order)
            ->forCard($this->card)
            ->create();

        $this->item = $item;
    }

    #[Test]
    public function itCreatesAGooglePassForTheCustomersCard(): void
    {
        $this->assertDatabaseEmpty(MobilePass::class);

        $pass = $this->getPass();

        $this->assertDatabaseCount(MobilePass::class, 1);
        $this->assertSame(Platform::Google, $pass->platform);
    }

    #[Test]
    public function itTiesThePassToTheCustomersDigitalItem(): void
    {
        $pass = $this->getPass();

        $this->assertSame(ShopOrderDigitalItem::class, $pass->model_type);
        $this->assertSame($this->item->id, $pass->model_id);
        $this->assertTrue($this->item->firstGooglePass()->is($pass));
    }

    #[Test]
    public function itGivesTheObjectADeterministicSuffix(): void
    {
        $this->assertSame(
            "3388000000000000001.spanish-travel-card-{$this->item->id}",
            $this->getPass()->pass_serial,
        );
    }

    #[Test]
    public function itListsThePassByItsEnglishNameAndTitlesItInTheNativeLanguage(): void
    {
        $payload = $this->getPass()->content['googleObjectPayload'];

        $this->assertSame('Spanish Travel Card', $payload['cardTitle']['defaultValue']['value']);
        $this->assertSame('Card title in the native language', $payload['header']['defaultValue']['value']);
        $this->assertSame(config('app.name'), $payload['subheader']['defaultValue']['value']);
        $this->assertSame(asset('images/wallet/seal-icon.png'), $payload['logo']['sourceUri']['uri']);
    }

    #[Test]
    public function itPutsTheBrandColourOnTheObject(): void
    {
        $this->assertSame(
            '#80CCFC',
            $this->getPass()->content['googleObjectPayload']['hexBackgroundColor'],
        );
    }

    #[Test]
    public function itPointsTheObjectAtTheCardsClass(): void
    {
        $this->assertSame(
            '3388000000000000001.spanish-travel-card',
            $this->getPass()->content['googleClassId'],
        );
    }

    #[Test]
    public function itReturnsTheExistingPassOnASecondCall(): void
    {
        $first = $this->getPass();
        $second = $this->getPass();

        $this->assertDatabaseCount(MobilePass::class, 1);
        $this->assertTrue($first->is($second));
    }

    #[Test]
    public function itFallsBackToTheLiveLogoLocally(): void
    {
        app()->detectEnvironment(fn () => 'local');

        $this->assertSame(
            'https://www.coeliacsanctuary.co.uk/images/seal-icon.png',
            $this->getPass()->content['googleObjectPayload']['logo']['sourceUri']['uri'],
        );
    }

    protected function getPass(): MobilePass
    {
        return $this->callAction(GetOrCreateGoogleWalletPassAction::class, $this->customer, $this->card);
    }
}
