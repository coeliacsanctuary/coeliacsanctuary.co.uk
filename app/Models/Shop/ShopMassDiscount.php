<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShopMassDiscount extends Model
{
    protected $casts = [
        'created' => 'bool',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /** @return BelongsToMany<ShopCategory, $this> */
    public function assignedCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            ShopCategory::class,
            'shop_mass_discount_categories',
            'mass_discount_id',
            'category_id',
        );
    }
}
