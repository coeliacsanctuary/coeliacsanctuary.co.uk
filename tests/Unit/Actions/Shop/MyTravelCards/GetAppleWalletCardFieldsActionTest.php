<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\MyTravelCards;

use App\Actions\Shop\MyTravelCards\GetAppleWalletCardFieldsAction;
use App\Models\Shop\ShopDigitalCard;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetAppleWalletCardFieldsActionTest extends TestCase
{
    protected ShopDigitalCard $card;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    #[Test]
    public function itReturnsAFieldForEverySectionOfTheCard(): void
    {
        $this->assertSame(
            ['card', 'cant_eat', 'can_eat', 'outro'],
            array_keys($this->fields()),
        );
    }

    #[Test]
    public function itLabelsTheIntroWithTheCardTitle(): void
    {
        $fields = $this->fields();

        $this->assertSame('Intro paragraph in the native language', $fields['card']['value']);
        $this->assertSame('Card title in the native language', $fields['card']['label']);
    }

    #[Test]
    public function itUsesTheCardsOwnEatLabels(): void
    {
        $fields = $this->fields();

        $this->assertSame('first item, second item, third item', $fields['cant_eat']['value']);
        $this->assertSame('Cannot eat label', $fields['cant_eat']['label']);

        $this->assertSame('safe item, another safe item', $fields['can_eat']['value']);
        $this->assertSame('Can eat label', $fields['can_eat']['label']);
    }

    #[Test]
    public function itFoldsTheThankYouIntoTheOutroWithoutALabel(): void
    {
        $outro = $this->fields()['outro'];

        $this->assertSame("Cross contamination paragraph\n\nThank you line", $outro['value']);
        $this->assertSame('', $outro['label']);
    }

    /** @return array<string, array{value: string, label: string}> */
    protected function fields(): array
    {
        return $this->callAction(GetAppleWalletCardFieldsAction::class, $this->card);
    }
}
