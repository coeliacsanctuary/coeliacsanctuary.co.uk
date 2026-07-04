<?php

declare(strict_types=1);

namespace App\DataObjects\Shop;

use App\Http\Requests\Shop\CompleteOrderRequest;

final readonly class PendingOrderShippingAddressDetails
{
    public function __construct(
        public string $line_1,
        public ?string $line_2,
        public ?string $line_3,
        public string $town,
        public string $county,
        public string $postcode,
        public string $country,
    ) {
        //
    }

    /** @return array{line_1: string, line_2: ?string, line_3: ?string, town: string, county: string, postcode: string, country: string} */
    public function toArray(): array
    {
        return [
            'line_1' => $this->line_1,
            'line_2' => $this->line_2,
            'line_3' => $this->line_3,
            'town' => $this->town,
            'county' => $this->county,
            'postcode' => $this->postcode,
            'country' => $this->country,
        ];
    }

    public static function createFromRequest(CompleteOrderRequest $request, string $country): static
    {
        return new self(
            line_1: $request->string('shipping.address_1')->title()->toString(),
            line_2: $request->has('shipping.address_2') ? $request->string('shipping.address_2')->title()->toString() : null,
            line_3: $request->has('shipping.address_3') ? $request->string('shipping.address_3')->title()->toString() : null,
            town: $request->string('shipping.town')->title()->toString(),
            county: $request->string('shipping.county')->title()->toString(),
            postcode: $request->string('shipping.postcode')->upper()->toString(),
            country: $country,
        );
    }
}
