<?php

declare(strict_types=1);

namespace App\Ai\Tools\PrepareRecommendedEatery;

use App\DataObjects\EatingOut\LatLng;
use App\Services\EatingOut\LocationSearchService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GeoLookup implements Tool
{
    public function description(): Stringable|string
    {
        return 'Perform a geocode lookup of a given address to get the latlng';
    }

    public function handle(Request $request): Stringable|string
    {
        /** @var LatLng $lookup */
        $lookup = app(LocationSearchService::class)->getLatLng($request->string('address')->toString());

        return (string) json_encode([
            'latitude' => $lookup->lat,
            'longitude' => $lookup->lng,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'address' => $schema->string()->required(),
        ];
    }
}
