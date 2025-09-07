<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Enums\Shop\OrderState;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * @property Carbon $expires_at
 */
class ShopOrderDownloadLink extends Model
{
    use HasUuids;

    protected static function booted(): void
    {
        self::saving(function (self $downloadLink): void {
            /** @var ?ShopOrder $order */
            $order = $downloadLink->order;

            if ( ! $order) {
                throw new RuntimeException('Cannot create download link for order that does not exist');
            }

            if ($order->state_id === OrderState::BASKET || $order->state_id === OrderState::EXPIRED) {
                throw new RuntimeException('Cannot create download link for order in basket');
            }

            if ($order->state_id === OrderState::PENDING) {
                throw new RuntimeException('Cannot create download link for order in pending state');
            }

            if ($order->state_id === OrderState::CANCELLED) {
                throw new RuntimeException('Cannot create download link for order that is cancelled');
            }

            if ( ! $order->has_digital_products) {
                throw new RuntimeException('Cannot create download link for order without digital products');
            }
        });

        static::saved(function (self $downloadLink): void {
            if ($downloadLink->expires_at->isPast()) {
                return;
            }

            self::query()
                ->where('order_id', $downloadLink->order_id)
                ->where('expires_at', '>', now())
                ->whereNot('id', $downloadLink->id)
                ->update(['expires_at' => now()]);
        });
    }

    public function newUniqueId(): string
    {
        return (string) Uuid::uuid4();
    }

    /** @return BelongsTo<ShopOrder, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'order_id');
    }

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'expires_at' => 'datetime',
        ];
    }
}
