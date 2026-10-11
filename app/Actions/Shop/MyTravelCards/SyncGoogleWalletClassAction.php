<?php

declare(strict_types=1);

namespace App\Actions\Shop\MyTravelCards;

use App\Models\Shop\ShopDigitalCard;
use Spatie\LaravelMobilePass\Builders\Google\GenericPassClass;

class SyncGoogleWalletClassAction
{
    public function __construct(protected GetWalletCardSectionsAction $getCardSections)
    {
    }

    public function handle(ShopDigitalCard $card): void
    {
        if ( ! config()->boolean('coeliac.google_wallet_enabled')) {
            return;
        }

        $class = GenericPassClass::make($card->slug)
            ->addLink(config()->string('app.url'), config()->string('app.name'));

        $sections = $this->getCardSections->handle($card);

        $modules = [
            $sections['card'],
            $sections['cant_eat'],
            $sections['can_eat'],
            $sections['outro']->withLabel(' '),
            $sections['thank_you'],
        ];

        foreach ($modules as $section) {
            $class->addTextModule($section->label, $section->value, $section->key);
        }

        $class->save();

        $card->updateQuietly(['google_wallet_class_id' => $class->id()]);
    }
}
