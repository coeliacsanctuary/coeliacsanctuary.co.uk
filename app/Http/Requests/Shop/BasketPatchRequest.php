<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use App\Actions\Shop\VerifyDiscountCodeAction;
use App\Enums\Shop\OrderState;
use App\Enums\Shop\ProductVariantType;
use App\Models\Shop\ShopDiscountCode;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopPostageCountry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use RuntimeException;

class BasketPatchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'postage_country_id' => ['nullable', Rule::exists(ShopPostageCountry::class, 'id')],
            'action' => ['nullable', 'in:increase,decrease'],
            'item_id' => ['nullable', 'required_with:action', 'numeric'],
            'discount' => ['nullable', 'bail', Rule::exists(ShopDiscountCode::class, 'code')->where(
                fn (DatabaseBuilder $builder) => $builder
                    ->where('start_at', '<=', now())
                    ->where('end_at', '>=', now())
            )],
            'contact' => ['array'],
            'contact.name' => ['required_with:contact', 'string'],
            'contact.email' => ['required_with:contact', 'string', 'email', 'confirmed'],
            'contact.phone' => ['nullable'],
        ];
    }

    public function messages()
    {
        return [
            'discount.exists' => 'Discount Code not found',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->missing('action')) {
                    return;
                }

                $itemInBasket = ShopOrderItem::query()
                    ->where('id', $this->integer('item_id'))
                    ->with(['variant'])
                    ->whereRelation('order', function (Builder $builder) {
                        /** @var Builder<ShopOrder> $builder */
                        return $builder
                            ->where('state_id', OrderState::BASKET)
                            ->where('token', $this->cookie('basket_token'));
                    })
                    ->first();

                if ( ! $itemInBasket) {
                    $validator->errors()->add('item_id', "This product isn't in your basket");

                    return;
                }

                if ($itemInBasket->variant?->variant_type === ProductVariantType::DIGITAL) {
                    $validator->errors()->add('item_id', "This product can't be altered");
                }
            },

            function (Validator $validator): void {
                if ($this->missing('discount') || $validator->errors()->has('discount')) {
                    return;
                }

                if ($this->string('discount')->toString() === '') {
                    $validator->errors()->add('discount', 'Please enter a discount code!');

                    return;
                }

                try {
                    $discountCode = ShopDiscountCode::query()
                        ->where('code', $this->string('discount')->toString())
                        ->withCount('used')
                        ->firstOrFail();

                    /** @var string $token */
                    $token = $this->cookie('basket_token');

                    app(VerifyDiscountCodeAction::class)->handle($discountCode, $token);
                } catch (ModelNotFoundException) {
                    $validator->errors()->add('discount', 'Discount code not found!');
                } catch (RuntimeException $exception) {
                    $validator->errors()->add('discount', $exception->getMessage());
                }
            },
        ];
    }
}
