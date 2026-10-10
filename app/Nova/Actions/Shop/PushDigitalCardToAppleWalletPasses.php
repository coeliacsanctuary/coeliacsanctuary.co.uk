<?php

declare(strict_types=1);

namespace App\Nova\Actions\Shop;

use App\Actions\Shop\MyTravelCards\SyncAppleWalletPassesForDigitalCardAction;
use App\Models\Shop\ShopDigitalCard;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;

class PushDigitalCardToAppleWalletPasses extends Action
{
    public $name = 'Push content to Apple Wallet passes';

    /**
     * @param  Collection<int, ShopDigitalCard>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(fn (ShopDigitalCard $card) => dispatch(
            fn () => app(SyncAppleWalletPassesForDigitalCardAction::class)->handle($card),
        ));

        return ActionResponse::message('Apple Wallet passes are queued to be updated');
    }
}
