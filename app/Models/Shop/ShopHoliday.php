<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $notice
 */
class ShopHoliday extends Model
{
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'ship_on' => 'date',
    ];

    /** @return Attribute<non-falsy-string, never> */
    public function notice(): Attribute
    {
        return Attribute::get(fn (): string => implode(' ', [
            "The shop is on holiday from {$this->start_date->format('l jS F')} until {$this->end_date->format('l jS F')}.",
            "Any orders placed during this time will be despatched on {$this->ship_on->format('l jS F')}.",
        ]));
    }
}
