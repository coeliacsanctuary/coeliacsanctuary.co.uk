<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopProduct;
use App\Support\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Money\Money;

/** @mixin ShopProduct */
class ShopProductApiResource extends JsonResource
{
    /** @return array{id: int, title: string, description: string, meta_description: string, link: string, main_image: string, price: string, created_at: Carbon} */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'meta_description' => $this->meta_description,
            'link' => route('shop.product', ['product' => $this]),
            'main_image' => $this->main_image,
            'price' => Helpers::formatMoney(Money::GBP($this->from_price)),
            'created_at' => $this->created_at,
        ];
    }
}
