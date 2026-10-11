<?php

declare(strict_types=1);

namespace App\Actions\Shop\MyTravelCards;

use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Support\Shop\Wallet\GenericPassBuilder;
use Spatie\LaravelMobilePass\Models\MobilePass;

class GetOrCreateGoogleWalletPassAction
{
    public function handle(ShopCustomer $customer, ShopDigitalCard $card): MobilePass
    {
        /** @var ShopOrderDigitalItem $item */
        $item = $customer
            ->ownedDigitalItems()
            ->where('digital_card_id', $card->id)
            ->firstOrFail();

        $existingPass = $item->firstGooglePass();

        if ($existingPass) {
            return $existingPass;
        }

        $pass = GenericPassBuilder::make()
            ->setClass($card->slug)
            ->setObjectSuffix("{$card->slug}-{$item->id}")
            ->setCardTitle($card->title)
            ->setHeader($card->card_title)
            ->setSubheader(config()->string('app.name'))
            ->setLogoUrl($this->logoUrl())
            ->setBackgroundColor('#80CCFC')
            ->save();

        $item->addMobilePass($pass);

        return $pass;
    }

    protected function logoUrl(): string
    {
        if (app()->isLocal()) {
            return 'https://www.coeliacsanctuary.co.uk/images/seal-icon.png';
        }

        return asset('images/wallet/seal-icon.png');
    }
}
