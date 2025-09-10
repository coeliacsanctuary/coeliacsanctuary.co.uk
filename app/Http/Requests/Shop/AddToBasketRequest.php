<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use App\Enums\Shop\ProductVariantType;
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

        return [
            'product_id' => ['required', 'numeric', Rule::in($productIds)],
            'variant_id' => ['required', 'numeric'],
            'quantity' => ['required', 'int', 'min:1', 'regex:/^[0-9]+$/'],
        ];
    }

    public function after(): array
    {
        return [
            // Valid Variant Id
            function (Validator $validator): void {
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
                $variant = ShopProductVariant::query()->find($this->integer('variant_id'));

                if ( ! $variant) {
                    $validator->errors()->add('variant_id', 'The product variant can\'t be found');
                }
            },

            // Quantity Available
            function (Validator $validator): void {
                $variant = ShopProductVariant::query()->find($this->integer('variant_id'));

                if($variant->variant_type === ProductVariantType::BUNDLE) {
                    $variant = ShopProductVariant::query()
                        ->where('product_id', $this->integer('product_id'))
                        ->where('variant_type', ProductVariantType::PHYSICAL)
                        ->first();
                }

                if ( ! $variant || $variant->quantity < $this->integer('quantity')) {
                    $validator->errors()->add('quantity', 'The product doesn\'t have the requested quantity available');
                }
            },

            // Variant is not digital
            function (Validator $validator): void {
                $variant = ShopProductVariant::query()->find($this->integer('variant_id'));

                if ($variant && $variant->variant_type === ProductVariantType::DIGITAL && $this->integer('quantity') > 1) {
                    $validator->errors()->add('quantity', 'Digital products can only be added in one quantity');
                }
            }
        ];
    }
}
