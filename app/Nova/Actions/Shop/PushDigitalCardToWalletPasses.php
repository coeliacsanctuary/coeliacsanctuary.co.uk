<?php

declare(strict_types=1);

namespace App\Nova\Actions\Shop;

use App\Actions\Shop\MyTravelCards\SyncAppleWalletPassesForDigitalCardAction;
use App\Actions\Shop\MyTravelCards\SyncGoogleWalletClassAction;
use App\Models\Shop\ShopDigitalCard;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;

class PushDigitalCardToWalletPasses extends Action
{
    public $name = 'Push content to wallet passes';

    /**
     * @param  Collection<int, ShopDigitalCard>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function (ShopDigitalCard $card): void {
            dispatch(fn () => app(SyncAppleWalletPassesForDigitalCardAction::class)->handle($card));
            dispatch(fn () => app(SyncGoogleWalletClassAction::class)->handle($card));
        });

        return ActionResponse::message('Wallet passes are queued to be updated');
    }
}
