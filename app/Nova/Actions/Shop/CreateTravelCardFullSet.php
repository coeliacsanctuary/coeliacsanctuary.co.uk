<?php

namespace App\Nova\Actions\Shop;

use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class CreateTravelCardFullSet extends Action
{
    public function handle(ActionFields $fields, Collection $models)
    {
        if(!$fields->quantity || !is_numeric($fields->quantity)) {
            return ActionResponse::danger('Quantity must be a number.');
        }

        $fullSet = ShopProduct::query()->find(22);

        $cardsToUpdate = ShopProduct::query()
            ->whereRelation('categories', 'id', 1)
            ->whereNot('id', 22)
            ->with(['variants'])
            ->get();

        try {
            $cardsToUpdate->each(function (ShopProduct $card) use ($fields) {
                if ($card->variants->first()->quantity < $fields->quantity) {
                    $suffix = $fields->quantity === 1 ? 'a full set' : "{$fields->quantity} full sets";

                    throw new Exception("Product {$card->title} does not have enough quantity to create {$suffix}.");
           }
            });
        } catch (Exception $e) {
            return ActionResponse::danger($e->getMessage());
        }

        try {
            DB::beginTransaction();

            ShopProductVariant::query()
                ->whereIn('product_id', $cardsToUpdate->pluck('id'))
                ->decrement('quantity', $fields->quantity);

            $fullSet->variants()->first()->increment('quantity', $fields->quantity);

            DB::commit();

            return ActionResponse::message('Travel card full sets created and all stock updated.');
        } catch (Exception) {
            DB::rollBack();
            return ActionResponse::danger('There was an error creating the full sets stock.');
        }
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Number::make('Quantity')
                ->min(1)
                ->default(1)
                ->required()
                ->help('The number of full sets to create, each other card will have this number removed from their quantity.'),
        ];
    }
}
