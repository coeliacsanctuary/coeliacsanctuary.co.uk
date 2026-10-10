<?php

declare(strict_types=1);

namespace App\Actions\Shop\MyTravelCards;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrderDigitalItem;
use Illuminate\Support\Collection;
use Spatie\LaravelMobilePass\Models\MobilePass;

class SyncAppleWalletPassesForDigitalCardAction
{
    public function __construct(protected GetAppleWalletCardFieldsAction $getCardFields)
    {
    }

    public function handle(ShopDigitalCard $card): int
    {
        $fields = $this->getCardFields->handle($card);

        $passes = $this->passesFor($card);

        $passes->each(function (MobilePass $pass) use ($fields): void {
            $builder = $pass->builder();

            foreach ($fields as $key => $field) {
                $builder->updateField($key, $field['value'], label: $field['label']);
            }

            $builder->save();
        });

        return $passes->count();
    }

    /** @return Collection<int, MobilePass> */
    protected function passesFor(ShopDigitalCard $card): Collection
    {
        /** @var Collection<int, MobilePass> $passes */
        $passes = $card
            ->orderDigitalItems()
            ->with('applePasses')
            ->get()
            ->flatMap(fn (ShopOrderDigitalItem $item) => $item->applePasses);

        return $passes;
    }
}
