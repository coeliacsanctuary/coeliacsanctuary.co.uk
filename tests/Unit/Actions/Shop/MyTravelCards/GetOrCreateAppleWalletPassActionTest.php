<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\MyTravelCards;

use App\Actions\Shop\MyTravelCards\GetOrCreateAppleWalletPassAction;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Tests\TestCase;

class GetOrCreateAppleWalletPassActionTest extends TestCase
{
    protected ShopCustomer $customer;

    protected ShopDigitalCard $card;

    protected ShopOrderDigitalItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = $this->create(ShopCustomer::class);

        /** @var ShopOrder $order */
        $order = $this->build(ShopOrder::class)->asPaid($this->customer)->create();

        $this->card = $this->build(ShopDigitalCard::class)->create([
            'title' => 'Spanish Travel Card',
            'slug' => 'spanish-travel-card',
            'language' => 'spanish',
            'card_title' => 'Card title in the native language',
            'card_intro' => 'Intro paragraph in the native language',
            'cant_eat_label' => 'Cannot eat label',
            'cant_eat_items' => 'first item, second item, third item',
            'can_eat_label' => 'Can eat label',
            'can_eat_items' => 'safe item, another safe item',
            'card_outro' => 'Cross contamination paragraph',
            'card_thank_you' => 'Thank you line',
        ]);

        /** @var ShopOrderDigitalItem $item */
        $item = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($order)
            ->forCard($this->card)
            ->create();

        $this->item = $item;
    }

    #[Test]
    public function itCreatesAnApplePassForTheCustomersCard(): void
    {
        $this->assertDatabaseEmpty(MobilePass::class);

        $pass = $this->getPass();

        $this->assertDatabaseCount(MobilePass::class, 1);
        $this->assertSame(Platform::Apple, $pass->platform);
    }

    #[Test]
    public function itTiesThePassToTheCustomersDigitalItem(): void
    {
        $pass = $this->getPass();

        $this->assertSame(ShopOrderDigitalItem::class, $pass->model_type);
        $this->assertSame($this->item->id, $pass->model_id);
        $this->assertTrue($this->item->firstApplePass()->is($pass));
    }

    #[Test]
    public function itGivesThePassADeterministicSerial(): void
    {
        $this->assertSame("spanish-travel-card-{$this->item->id}", $this->getPass()->pass_serial);
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
    public function itPutsTheEnglishCoverOnTheFrontOfThePass(): void
    {
        $content = $this->getPass()->content;

        $this->assertSame('Spanish', $this->fieldsIn($content, 'headerFields')['language']['value']);
        $this->assertSame('Gluten Free Travel Card', $this->fieldsIn($content, 'primaryFields')['card_type']['value']);
        $this->assertSame('Tap ••• then ⓘ Pass Details', $this->fieldsIn($content, 'secondaryFields')['instruction']['value']);
    }

    #[Test]
    public function itPutsTheWholeCardOnTheBackOfThePass(): void
    {
        $backFields = $this->fieldsIn($this->getPass()->content, 'backFields');

        $this->assertSame(['card', 'cant_eat', 'can_eat', 'outro'], array_keys($backFields));

        $this->assertSame('Card title in the native language', $backFields['card']['label']);
        $this->assertSame('Cannot eat label', $backFields['cant_eat']['label']);
        $this->assertSame('Can eat label', $backFields['can_eat']['label']);
    }

    #[Test]
    public function itLeavesTheOutroLabelEmptyAndFoldsTheThankYouIntoIt(): void
    {
        $outro = $this->fieldsIn($this->getPass()->content, 'backFields')['outro'];

        $this->assertSame('', $outro['label']);
        $this->assertSame("Cross contamination paragraph\n\nThank you line", $outro['value']);
    }

    #[Test]
    public function itProhibitsSharingThePass(): void
    {
        $this->assertTrue($this->getPass()->content['sharingProhibited']);
    }

    #[Test]
    public function itNamesTheDownloadAfterTheCardLanguage(): void
    {
        $this->assertSame('coeliac-travel-card-spanish', $this->getPass()->download_name);
    }

    #[Test]
    public function itDescribesThePassWithTheEnglishCardTitle(): void
    {
        $this->assertSame('Spanish Travel Card', $this->getPass()->content['description']);
    }

    protected function getPass(): MobilePass
    {
        return $this->callAction(GetOrCreateAppleWalletPassAction::class, $this->customer, $this->card);
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, array<string, mixed>>
     */
    protected function fieldsIn(array $content, string $zone): array
    {
        /** @var array<int, array<string, mixed>> $fields */
        $fields = $content['generic'][$zone];

        return Collection::make($fields)->keyBy('key')->toArray();
    }
}
