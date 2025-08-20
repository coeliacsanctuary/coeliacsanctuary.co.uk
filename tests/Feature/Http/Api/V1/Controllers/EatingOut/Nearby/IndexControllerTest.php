<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Nearby;

use App\Pipelines\EatingOut\GetEateries\GetNearbyEateriesPipeline;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    #[Test]
    public function itErrorsWithOutASource(): void
    {
        $this->getJson(route('api.v1.eating-out.nearby'))->assertForbidden();
    }

    #[Test]
    public function itErrorsWithoutALatLng(): void
    {
        $this->makeRequest(null)->assertJsonValidationErrorFor('latlng');
    }

    #[Test]
    public function itCallsTheGetNearbyEateriesPipeline(): void
    {
        $this->expectPipelineToRun(GetNearbyEateriesPipeline::class, new LengthAwarePaginator([], 0, 10));

        $this->makeRequest('1,2')->assertOk();
    }

    protected function makeRequest(?string $latlng, string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.nearby', ['latlng' => $latlng]),
            ['x-coeliac-source' => $source],
        );
    }
}
