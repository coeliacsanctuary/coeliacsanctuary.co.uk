<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\EmailDataCast;
use App\Models\Shop\ShopCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NotificationEmail extends Model
{
    protected $casts = [
        'data' => EmailDataCast::class,
    ];

    protected static function booted(): void
    {
        self::creating(function (self $notificationEmail): NotificationEmail {
            if ( ! $notificationEmail->key) {
                $notificationEmail->key = Str::uuid()->toString();
            }

            return $notificationEmail;
        });
    }

    /** @return BelongsTo<ShopCustomer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(ShopCustomer::class, 'user_id');
    }
}
