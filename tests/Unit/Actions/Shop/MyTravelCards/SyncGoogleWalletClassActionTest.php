<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\MyTravelCards;

use App\Actions\Shop\MyTravelCards\SyncGoogleWalletClassAction;
use App\Models\Shop\ShopDigitalCard;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncGoogleWalletClassActionTest extends TestCase
{
    protected ShopDigitalCard $card;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        cache()->put('mobile-pass.google.access-token', 'test-token', 3600);

        config()->set('mobile-pass.google.issuer_id', '3388000000000000001');

        config()->set('coeliac.google_wallet_enabled', false);

        $this->card = $this->build(ShopDigitalCard::class)->create([
            'title' => 'Spanish Travel Card',
            'slug' => 'spanish-travel-card',
            'card_title' => 'Card title in the native language',
            'card_intro' => 'Intro paragraph in the native language',
            'cant_eat_label' => 'Cannot eat label',
            'cant_eat_items' => 'first item, second item',
            'can_eat_label' => 'Can eat label',
            'can_eat_items' => 'safe item, another safe item',
            'google_wallet_class_id' => null,
        ]);

        config()->set('coeliac.google_wallet_enabled', true);
    }

    #[Test]
    public function itWritesTheClassIdBackToTheCard(): void
    {
        $this->callAction(SyncGoogleWalletClassAction::class, $this->card);

        $this->assertSame(
            '3388000000000000001.spanish-travel-card',
            $this->card->fresh()->google_wallet_class_id,
        );
    }

    #[Test]
    public function itSendsTheCardSectionsAsTextModules(): void
    {
        $this->callAction(SyncGoogleWalletClassAction::class, $this->card);

        Http::assertSent(function ($request) {
            $modules = collect($request['textModulesData'] ?? [])->keyBy('id');

            return $modules->count() === 5
                && $modules['outro']['header'] === ' '
                && $modules['thank_you']['header'] === ''
                && $modules['card']['body'] === 'Intro paragraph in the native language'
                && $modules['cant_eat']['header'] === 'Cannot eat label'
                && $modules['cant_eat']['body'] === 'first item, second item'
                && $modules['can_eat']['header'] === 'Can eat label';
        });
    }

    #[Test]
    public function itLinksToTheWebsite(): void
    {
        $this->callAction(SyncGoogleWalletClassAction::class, $this->card);

        Http::assertSent(function ($request) {
            $link = $request['linksModuleData']['uris'][0] ?? null;

            return $link['uri'] === config()->string('app.url')
                && $link['description'] === config('app.name');
        });
    }

    #[Test]
    public function itDoesntSendFieldsAGenericClassWouldDiscard(): void
    {
        $this->callAction(SyncGoogleWalletClassAction::class, $this->card);

        Http::assertSent(fn ($request) => ! isset($request['cardTitle'], $request['header'], $request['hexBackgroundColor'], $request['issuerName']));
    }

    #[Test]
    public function itDoesNothingWhenGoogleWalletIsDisabled(): void
    {
        config()->set('coeliac.google_wallet_enabled', false);

        $this->callAction(SyncGoogleWalletClassAction::class, $this->card);

        Http::assertNothingSent();
        $this->assertNull($this->card->fresh()->google_wallet_class_id);
    }

    #[Test]
    public function itReSyncsACardThatAlreadyHasAClass(): void
    {
        $this->card->updateQuietly(['google_wallet_class_id' => '3388000000000000001.spanish-travel-card']);

        $this->callAction(SyncGoogleWalletClassAction::class, $this->card);

        Http::assertSentCount(1);
        $this->assertSame(
            '3388000000000000001.spanish-travel-card',
            $this->card->fresh()->google_wallet_class_id,
        );
    }
}
