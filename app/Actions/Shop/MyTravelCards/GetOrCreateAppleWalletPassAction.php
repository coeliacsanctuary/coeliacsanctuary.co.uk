<?php

declare(strict_types=1);

namespace App\Actions\Shop\MyTravelCards;

use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrderDigitalItem;
use Illuminate\Support\Str;
use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;
use Spatie\LaravelMobilePass\Models\MobilePass;

class GetOrCreateAppleWalletPassAction
{
    public function __construct(protected GetWalletCardSectionsAction $getCardSections)
    {
    }

    public function handle(ShopCustomer $customer, ShopDigitalCard $card): MobilePass
    {
        /** @var ShopOrderDigitalItem $item */
        $item = $customer
            ->ownedDigitalItems()
            ->where('digital_card_id', $card->id)
            ->firstOrFail();

        $existingPass = $item->firstApplePass();

        if ($existingPass) {
            return $existingPass;
        }

        /** @var string $organisationName */
        $organisationName = config('mobile-pass.apple.organization_name');

        $builder = GenericPassBuilder::make()
            ->setOrganizationName($organisationName)
            ->setSerialNumber("{$card->slug}-{$item->id}")
            ->setDescription($card->title)
            ->setDownloadName(Str::slug("coeliac-travel-card-{$card->language}"))
            ->setLogoImage(
                public_path('images/wallet/logo.png'),
                public_path('images/wallet/logo@2x.png'),
                public_path('images/wallet/logo@3x.png'),
            )
            ->setIconImage(public_path('apple-touch-icon.png'))
            ->setSharingProhibited(true)
            ->setBackgroundColor('#80CCFC')
            ->setForegroundColor('#000000')
            ->setLabelColor('#29719f')
            ->addHeaderField('language', Str::title($card->language))
            ->addField('card_type', 'Gluten Free Travel Card')
            ->addSecondaryField('instruction', 'Tap ••• then ⓘ Pass Details', label: 'To show restaurant staff');

        $sections = $this->getCardSections->handle($card);

        $backFields = [
            $sections['card'],
            $sections['cant_eat'],
            $sections['can_eat'],
            $sections['outro']->appendedWith($sections['thank_you']),
        ];

        foreach ($backFields as $section) {
            $builder->addBackField($section->key, $section->value, $section->label);
        }

        $pass = $builder->save();

        $item->addMobilePass($pass);

        return $pass;
    }
}
