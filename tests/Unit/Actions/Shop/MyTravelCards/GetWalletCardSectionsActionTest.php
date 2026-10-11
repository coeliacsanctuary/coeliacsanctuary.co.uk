<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\MyTravelCards;

use App\Actions\Shop\MyTravelCards\GetWalletCardSectionsAction;
use App\DataObjects\Shop\WalletCardSection;
use App\Models\Shop\ShopDigitalCard;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetWalletCardSectionsActionTest extends TestCase
{
    protected ShopDigitalCard $card;

    protected function setUp(): void
    {
        parent::setUp();

        $this->card = $this->build(ShopDigitalCard::class)->create([
            'card_title' => 'Card title in the native language',
            'card_intro' => 'Intro paragraph in the native language',
            'cant_eat_label' => 'Cannot eat label',
            'cant_eat_items' => 'first item, second item',
            'can_eat_label' => 'Can eat label',
            'can_eat_items' => 'safe item, another safe item',
            'card_outro' => 'Cross contamination paragraph',
            'card_thank_you' => 'Thank you line',
        ]);
    }

    #[Test]
    public function itReturnsASectionForEveryPartOfTheCard(): void
    {
        $this->assertSame(
            ['card', 'cant_eat', 'can_eat', 'outro', 'thank_you'],
            $this->sections()->keys()->all(),
        );
    }

    #[Test]
    public function itLabelsTheIntroWithTheCardTitle(): void
    {
        $section = $this->sections()['card'];

        $this->assertSame('Intro paragraph in the native language', $section->value);
        $this->assertSame('Card title in the native language', $section->label);
    }

    #[Test]
    public function itUsesTheCardsOwnEatLabels(): void
    {
        $sections = $this->sections();

        $this->assertSame('first item, second item', $sections['cant_eat']->value);
        $this->assertSame('Cannot eat label', $sections['cant_eat']->label);

        $this->assertSame('safe item, another safe item', $sections['can_eat']->value);
        $this->assertSame('Can eat label', $sections['can_eat']->label);
    }

    #[Test]
    public function itLeavesTheOutroAndThankYouUnlabelled(): void
    {
        $sections = $this->sections();

        $this->assertSame('Cross contamination paragraph', $sections['outro']->value);
        $this->assertSame('', $sections['outro']->label);

        $this->assertSame('Thank you line', $sections['thank_you']->value);
        $this->assertSame('', $sections['thank_you']->label);
    }

    #[Test]
    public function aSectionCanBeAppendedToAnother(): void
    {
        $sections = $this->sections();

        $combined = $sections['outro']->appendedWith($sections['thank_you']);

        $this->assertSame("Cross contamination paragraph\n\nThank you line", $combined->value);
        $this->assertSame('outro', $combined->key);
    }

    #[Test]
    public function aSectionCanBeRelabelled(): void
    {
        $relabelled = $this->sections()['outro']->withLabel(' ');

        $this->assertSame(' ', $relabelled->label);
        $this->assertSame('Cross contamination paragraph', $relabelled->value);
    }

    /** @return Collection<string, WalletCardSection> */
    protected function sections(): Collection
    {
        return $this->callAction(GetWalletCardSectionsAction::class, $this->card);
    }
}
