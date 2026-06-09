<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ShopPrice extends Model
{
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /** @return MorphTo<Model, $this> */
    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }
}
