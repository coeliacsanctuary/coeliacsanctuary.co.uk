<?php

declare(strict_types=1);

namespace App\DataObjects\EatingOut;

use Illuminate\Support\Arr;

class AiPreparedRecommendation
{
    public function __construct(
        public readonly ?string $placeName,
        public readonly ?string $placeAddress,
        public readonly ?string $placeCountry,
        public readonly ?string $placeCounty,
        public readonly ?string $placeTown,
        public readonly ?string $placeArea,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly ?string $phoneNumber,
        public readonly ?string $website,
        public readonly ?string $facebook,
        public readonly ?string $instagram,
        public readonly ?string $eateryType,
        public readonly ?string $venueType,
        public readonly ?string $cuisine,
        public readonly ?string $info,
        public readonly ?array $features,
        public readonly string $explanation,
        public bool $isEligible,
    ) {
    }

    public static function fromArray(array $response): self
    {
        $data = Arr::get($response, 'data', []);

        $resolveFromData = function ($key, $callback) use ($data): mixed {
            if (Arr::get($data, $key) === null) {
                return null;
            }

            return $callback($data, $key);
        };

        return new self(
            placeName: $resolveFromData('place_name', Arr::string(...)),
            placeAddress: $resolveFromData('place_address', Arr::string(...)),
            placeCountry: $resolveFromData('place_country', Arr::string(...)),
            placeCounty: $resolveFromData('place_county', Arr::string(...)),
            placeTown: $resolveFromData('place_town', Arr::string(...)),
            placeArea: $resolveFromData('place_area', Arr::string(...)),
            latitude: $resolveFromData('latitude', Arr::float(...)),
            longitude: $resolveFromData('longitude', Arr::float(...)),
            phoneNumber: $resolveFromData('phone_number', Arr::string(...)),
            website: $resolveFromData('website', Arr::string(...)),
            facebook: $resolveFromData('facebook', Arr::string(...)),
            instagram: $resolveFromData('instagram', Arr::string(...)),
            eateryType: $resolveFromData('eatery_Type', Arr::string(...)),
            venueType: $resolveFromData('venue_type', Arr::string(...)),
            cuisine: $resolveFromData('cuisine', Arr::string(...)),
            info: $resolveFromData('info', Arr::string(...)),
            features: $resolveFromData('features', Arr::array(...)),
            explanation: Arr::string($response, 'explanation'),
            isEligible: Arr::boolean($response, 'is_eligible')
        );
    }
}
