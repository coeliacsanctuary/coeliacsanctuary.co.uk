<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopHoliday;

class GetActiveShopHolidayAction
{
    public function handle(): ?ShopHoliday
    {
        return ShopHoliday::query()
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->first();
    }
}
