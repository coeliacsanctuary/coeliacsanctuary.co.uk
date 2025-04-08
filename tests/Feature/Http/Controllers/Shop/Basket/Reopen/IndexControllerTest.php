<?php

namespace Tests\Feature\Http\Controllers\Shop\Basket\Reopen;

use App\Actions\Shop\ReopenBasketAction;
use App\Models\Shop\ShopOrder;
use Carbon\Carbon;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase{
    #[Test]
    public function itErrorsIfTheSignatureDoesntMatch(): void
    {
        $basket = $this->build(ShopOrder::class)->asExpired()->beenSentAbandonedBasketEmail()->create();

        $this->get(route('shop.basket.reopen', $basket))->assertInertia(fn(AssertableInertia $assert) => $assert->component('Shop/LinkExpired'));
    }

    #[Test]
    public function itErrorsIfTheBasketDoesntExist(): void
    {
        $basket = $this->build(ShopOrder::class)->make();

        $this->getSignedLink($basket)->assertNotFound();
    }

    #[Test]
    public function itErrorsIfTheBasketIsNotInExpiredState(): void
    {
        $basket = $this->build(ShopOrder::class)->asBasket()->beenSentAbandonedBasketEmail()->create();

        $this->getSignedLink($basket)->assertNotFound();
    }

    #[Test]
    public function itErrorsIfTheBasketHasntHadTheEmailSent(): void
    {
        $basket = $this->build(ShopOrder::class)->asExpired()->create();

        $this->getSignedLink($basket)->assertNotFound();
    }

    #[Test]
    public function itCallsTheReopenBasketAction(): void
    {
        $basket = $this->build(ShopOrder::class)->asExpired()->beenSentAbandonedBasketEmail()->create();

        $this->expectAction(ReopenBasketAction::class, return: collect());

        $this->getSignedLink($basket)->assertRedirect();
    }

    #[Test]
    public function itRedirectsToTheCheckoutPage(): void
    {
        $basket = $this->build(ShopOrder::class)->asExpired()->beenSentAbandonedBasketEmail()->create();

        $this->getSignedLink($basket)->assertRedirectToRoute('shop.basket.checkout');
    }

    protected function getSignedLink(ShopOrder $basket): TestResponse
    {
        return $this->get(resolve(UrlGenerator::class)->temporarySignedRoute(
            'shop.basket.reopen',
            Carbon::now()->addDay(),
            ['basket' => $basket]
        ));
    }

}
