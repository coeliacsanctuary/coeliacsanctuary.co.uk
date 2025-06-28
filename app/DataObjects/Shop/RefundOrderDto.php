<?php

declare(strict_types=1);

namespace App\DataObjects\Shop;

readonly class RefundOrderDto
{
    public function __construct(
        public int $amount,
        public string $note,
        public bool $cancelOrder,
        public bool $notifyCustomer,
        public ?string $reason,
    ) {
        //
    }
}
