<?php

declare(strict_types=1);

namespace App\Actions\Shop\MyTravelCards;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrderDigitalItem;
use Illuminate\Support\Collection;
use Spatie\LaravelMobilePass\Models\MobilePass;

class SyncAppleWalletPassesForDigitalCardAction
{
    public function __construct(protected GetWalletCardSectionsAction $getCardSections)
    {
    }

    public function handle(ShopDigitalCard $card): int
    {
        $sections = $this->getCardSections->handle($card);

        $backFields = [
            $sections['card'],
            $sections['cant_eat'],
            $sections['can_eat'],
            $sections['outro']->appendedWith($sections['thank_you']),
        ];

        $passes = $this->passesFor($card);

        $passes->each(function (MobilePass $pass) use ($backFields): void {
            $builder = $pass->builder();

            foreach ($backFields as $section) {
                $builder->updateField($section->key, $section->value, label: $section->label);
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
