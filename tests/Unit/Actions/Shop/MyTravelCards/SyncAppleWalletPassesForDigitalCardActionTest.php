<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\MyTravelCards;

use App\Actions\Shop\MyTravelCards\GetOrCreateAppleWalletPassAction;
use App\Actions\Shop\MyTravelCards\SyncAppleWalletPassesForDigitalCardAction;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Spatie\LaravelMobilePass\Jobs\PushPassUpdateJob;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Tests\TestCase;

class SyncAppleWalletPassesForDigitalCardActionTest extends TestCase
{
    protected ShopCustomer $customer;

    protected ShopDigitalCard $card;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();

        $this->customer = $this->create(ShopCustomer::class);

        $this->card = $this->card([
            'title' => 'Spanish Travel Card',
            'slug' => 'spanish-travel-card',
            'cant_eat_label' => 'Cannot eat label',
            'cant_eat_items' => 'first item, second item',
        ]);
    }

    #[Test]
    public function itRebuildsTheBackFieldsOfEveryPassIssuedForTheCard(): void
    {
        $pass = $this->issuePassFor($this->card);

        $this->card->update(['cant_eat_items' => 'first item, second item, third item, fourth item']);

        $this->callAction(SyncAppleWalletPassesForDigitalCardAction::class, $this->card);

        $this->assertSame('first item, second item, third item, fourth item', $this->backFields($pass->refresh())['cant_eat']['value']);
    }

    #[Test]
    public function itRebuildsTheLabelsTooWhenTheCardsEatLabelsChange(): void
    {
        $pass = $this->issuePassFor($this->card);

        $this->card->update(['cant_eat_label' => 'Changed cannot eat label']);

        $this->callAction(SyncAppleWalletPassesForDigitalCardAction::class, $this->card);

        $this->assertSame('Changed cannot eat label', $this->backFields($pass->refresh())['cant_eat']['label']);
    }

    #[Test]
    public function itUpdatesEveryCustomerHoldingTheCard(): void
    {
        $firstPass = $this->issuePassFor($this->card);

        $otherCustomer = $this->create(ShopCustomer::class);
        $secondPass = $this->issuePassFor($this->card, $otherCustomer);

        $this->card->update(['cant_eat_items' => 'first item, second item, third item']);

        $this->assertSame(2, $this->callAction(SyncAppleWalletPassesForDigitalCardAction::class, $this->card));

        $this->assertSame('first item, second item, third item', $this->backFields($firstPass->refresh())['cant_eat']['value']);
        $this->assertSame('first item, second item, third item', $this->backFields($secondPass->refresh())['cant_eat']['value']);
    }

    #[Test]
    public function itLeavesPassesForOtherCardsAlone(): void
    {
        $otherCard = $this->card([
            'title' => 'French Travel Card',
            'slug' => 'french-travel-card',
            'cant_eat_items' => 'other card first item, other card second item',
        ]);

        $otherPass = $this->issuePassFor($otherCard);

        $this->card->update(['cant_eat_items' => 'first item, second item, third item']);

        $this->callAction(SyncAppleWalletPassesForDigitalCardAction::class, $this->card);

        $this->assertSame('other card first item, other card second item', $this->backFields($otherPass->refresh())['cant_eat']['value']);
    }

    #[Test]
    public function itPushesTheUpdateOutToApple(): void
    {
        $this->issuePassFor($this->card);

        Queue::fake();

        $this->card->update(['cant_eat_items' => 'first item, second item, third item']);

        $this->callAction(SyncAppleWalletPassesForDigitalCardAction::class, $this->card);

        Queue::assertPushed(PushPassUpdateJob::class);
    }

    #[Test]
    public function itDoesNothingWhenNoPassesHaveBeenIssued(): void
    {
        $this->assertSame(0, $this->callAction(SyncAppleWalletPassesForDigitalCardAction::class, $this->card));
    }

    /** @param array<string, string> $attributes */
    protected function card(array $attributes): ShopDigitalCard
    {
        /** @var ShopDigitalCard $card */
        $card = $this->build(ShopDigitalCard::class)->create($attributes);

        return $card;
    }

    protected function issuePassFor(ShopDigitalCard $card, ?ShopCustomer $customer = null): MobilePass
    {
        $customer ??= $this->customer;

        /** @var ShopOrder $order */
        $order = $this->build(ShopOrder::class)->asPaid($customer)->create();

        $this->build(ShopOrderDigitalItem::class)
            ->inOrder($order)
            ->forCard($card)
            ->create();

        return $this->callAction(GetOrCreateAppleWalletPassAction::class, $customer, $card);
    }

    /** @return array<string, array<string, mixed>> */
    protected function backFields(MobilePass $pass): array
    {
        /** @var array<int, array<string, mixed>> $fields */
        $fields = $pass->content['generic']['backFields'];

        return Collection::make($fields)->keyBy('key')->toArray();
    }
}
