<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AddToBasketRequest extends FormRequest
{
    public function rules(): array
    {
        $productIds = ShopProduct::query()->pluck('id');
        $digitalCardIds = ShopDigitalCard::query()->pluck('id');

        return [
            'product_id' => ['bail', 'required_with_all:variant_id,quantity', 'numeric', Rule::in($productIds)],
            'variant_id' => ['bail', 'required_with_all:product_id,quantity', 'numeric'],
            'digital_card_id' => ['bail', 'required_without_all:product_id,variant_id,quantity,digital_card_ids', 'numeric', Rule::in($digitalCardIds)],
            'digital_card_ids' => ['bail', 'required_without_all:product_id,variant_id,quantity,digital_card_id', 'array'],
            'digital_card_ids.*' => ['bail', 'numeric', Rule::in($digitalCardIds)],
            'quantity' => ['bail', 'required_with_all:product_id,variant_id', 'int', 'min:1', 'regex:/^[0-9]+$/'],
        ];
    }

    public function isPhysicalProduct(): bool
    {
        return $this->filled('product_id');
    }

    public function isDigitalProduct(): bool
    {
        return $this->filled('digital_card_id') || $this->filled('digital_card_ids');
    }

    /** @return list<int> */
    public function digitalCardIds(): array
    {
        /** @var array<int, int|string> $ids */
        $ids = $this->input('digital_card_ids', []);

        if ($this->filled('digital_card_id')) {
            $ids[] = $this->integer('digital_card_id');
        }

        return array_values(array_unique(array_map(fn (int|string $id): int => (int) $id, $ids)));
    }

    public function after(): array
    {
        return [
            // Valid Variant Id
            function (Validator $validator): void {
                if ($this->isNotFilled('product_id')) {
                    return;
                }

                $product = ShopProduct::query()->find($this->integer('product_id'));

                if ( ! $product) {
                    return;
                }

                $variants = $product
                    ->variants()
                    ->where('live', true)
                    ->pluck('id')
                    ->toArray();

                if ( ! in_array($this->integer('variant_id'), $variants)) {
                    $validator->errors()->add('variant_id', "The given {$product->variant_title} could not be found");
                }
            },

            // Variant is live
            function (Validator $validator): void {
                if ($this->isNotFilled('variant_id')) {
                    return;
                }

                $variant = ShopProductVariant::query()->find($this->integer('variant_id'));

                if ( ! $variant) {
                    $validator->errors()->add('variant_id', 'The product variant can\'t be found');
                }
            },

            // Quantity Available
            function (Validator $validator): void {
                if ($this->isNotFilled('product_id')) {
                    return;
                }

                $variant = ShopProductVariant::query()->find($this->integer('variant_id'));

                if ( ! $variant || $variant->quantity < $this->integer('quantity')) {
                    $validator->errors()->add('quantity', 'The product doesn\'t have the requested quantity available');
                }
            },
        ];
    }
}
