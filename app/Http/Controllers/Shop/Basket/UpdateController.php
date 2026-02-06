<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Basket;

use App\Actions\Shop\AlterItemQuantityAction;
use App\Actions\Shop\Checkout\CreateCustomerAction;
use App\Actions\Shop\ResolveBasketAction;
use App\DataObjects\Shop\PendingOrderCustomerDetails;
use App\Exceptions\QuantityException;
use App\Http\Requests\Shop\BasketPatchRequest;
use App\Models\Shop\ShopOrder;
use Exception;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateController
{
    public function __invoke(BasketPatchRequest $request, ResolveBasketAction $resolveBasketAction): RedirectResponse|Response
    {
        /** @var string | null $token */
        $token = $request->cookies->get('basket_token');

        /** @var ShopOrder | null $basket */
        $basket = $resolveBasketAction->handle($token, false);

        if ( ! $basket) {
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            if ($request->has('postage_country_id')) {
                $basket->update([
                    'postage_country_id' => $request->integer('postage_country_id'),
                ]);
            }

            if ($request->has('action')) {
                $orderItem = $basket->items()->findOrFail($request->integer('item_id'));

                app(AlterItemQuantityAction::class)->handle($orderItem, $request->string('action')->toString());
            }

            if ($request->has('discount')) {
                $request->session()->put('discountCode', app(Encrypter::class)->encrypt($request->string('discount')->toString()));
            }

            if ($request->has('contact')) {
                $customer = app(CreateCustomerAction::class)->handle(PendingOrderCustomerDetails::createFromRequest($request));

                $basket->update([
                    'customer_id' => $customer->id,
                    'newsletter_signup' => $request->boolean('contact.subscribeToNewsletter'),
                ]);
            }

            DB::commit();
        } catch (QuantityException) {
            DB::rollBack();

            throw ValidationException::withMessages(['quantity' => 'not enough quantity available']);
        } catch (Exception) {
            DB::rollBack();
        }

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->back();
    }
}
