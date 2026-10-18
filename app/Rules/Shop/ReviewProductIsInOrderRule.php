<?php

declare(strict_types=1);

namespace App\Rules\Shop;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderReviewInvitation;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ReviewProductIsInOrderRule implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var int $value */

        /** @var ShopOrderReviewInvitation $invitation */
        $invitation = app(Request::class)->route('invitation');

        /** @var ShopOrder $order */
        $order = $invitation->order;

        $typeAttribute = Str::of($attribute)->beforeLast('.')->append('.type')->toString();

        if (Arr::get($this->data, $typeAttribute) === 'physical') {
            $products = $order->items->pluck('product_id')->values()->toArray();

            if ( ! in_array($value, $products, true)) {
                $fail('This product isn\'t in your order');

                return;
            }
        }

        if (Arr::get($this->data, $typeAttribute) === 'digital') {
            $digitalCards = $order->digitalItems->pluck('digital_card_id')->values()->toArray();

            if ( ! in_array($value, $digitalCards, true)) {
                $fail('This product isn\'t in your order');

                return;
            }
        }
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
