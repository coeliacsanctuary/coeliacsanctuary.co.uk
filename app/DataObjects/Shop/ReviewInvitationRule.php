<?php

declare(strict_types=1);

namespace App\DataObjects\Shop;

use App\Models\Shop\ShopOrder;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class ReviewInvitationRule
{
    /** @param Closure(Builder<ShopOrder>): Builder<ShopOrder> $pipe */
    public function __construct(
        public CarbonInterface $minimumDate,
        public Closure $pipe,
        public string $notificationDelayText,
    ) {
        //
    }
}
