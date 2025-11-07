<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\VenueTypes;

use App\Enums\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->create(EateryVenueType::class, 5);
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.venue-types'))->assertForbidden();
    }

    #[Test]
    public function itReturnsADataKey(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => []]);
    }

    #[Test]
    public function itReturnsTheVenueTypesFormattedCorrectly(): void
    {
        $venueTypes = EateryVenueType::query()
            ->orderBy('venue_type')
            ->get()
            ->groupBy('type_id')
            ->map(fn (Collection $options, int $typeId) => [
                'label' => Str::of(EateryType::from($typeId)->name)->title()->plural()->toString(),
                'options' => $options
                    ->map(fn (EateryVenueType $eateryVenueType) => [
                        'label' => $eateryVenueType->venue_type,
                        'value' => $eateryVenueType->id,
                    ]),
            ])
            ->values();

        $this->makeRequest()
            ->assertOk()
            ->assertExactJson(['data' => $venueTypes]);
    }

    protected function makeRequest(string $source = 'bar'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.venue-types'),
            ['x-coeliac-source' => $source],
        );
    }
}
