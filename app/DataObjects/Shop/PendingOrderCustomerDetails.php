<?php

declare(strict_types=1);

namespace App\DataObjects\Shop;

use App\Http\Requests\Shop\BasketPatchRequest;
use App\Http\Requests\Shop\CompleteOrderRequest;

final readonly class PendingOrderCustomerDetails
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone = null,
    ) {
        //
    }

    public static function createFromRequest(CompleteOrderRequest|BasketPatchRequest $request): self
    {
        return new self(
            name: $request->string('contact.name')->title()->toString(),
            email: $request->string('contact.email')->toString(),
            phone: $request->has('contact.phone') ? $request->string('contact.phone')->toString() : null,
        );
    }
}
