<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ShopCategory */
class ShopCategoryIndexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'link' => $this->link,
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'travelCardSearch' => $this->id === 1 || $this->id === 11,
        ];
    }
}
