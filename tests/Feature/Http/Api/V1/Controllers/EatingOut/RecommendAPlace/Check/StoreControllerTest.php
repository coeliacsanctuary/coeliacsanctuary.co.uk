<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\RecommendAPlace\Check;

use App\DataObjects\EatingOut\RecommendAPlaceExistsCheckData;
use App\Pipelines\EatingOut\CheckRecommendedPlace\CheckRecommendedPlacePipeline;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->postJson(route('api.v1.eating-out.recommend-a-place.check.store'))->assertForbidden();
    }

    #[Test]
    public function itCallsTheCheckRecommendedPlacePipeline(): void
    {
        $this->mock(CheckRecommendedPlacePipeline::class)
            ->shouldReceive('run')
            ->once()
            ->andReturn(new RecommendAPlaceExistsCheckData(id: 1));

        $this->makeRequest($this->paramFactory())->assertOk();
    }

    #[Test]
    public function itReturnsADataArrayIfTheAResultIsFound(): void
    {
        $this->mock(CheckRecommendedPlacePipeline::class)
            ->shouldReceive('run')
            ->once()
            ->andReturn(new RecommendAPlaceExistsCheckData(id: 1, reason: 'foo', branchId: 2, label: 'bar'));

        $this->makeRequest($this->paramFactory())
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'result' => 'foo',
                    'id' => 1,
                    'branchId' => 2,
                    'label' => 'bar',
                ],
            ]);
    }

    #[Test]
    public function itReturnsNoContentIfTheNoResultIsFound(): void
    {
        $this->mock(CheckRecommendedPlacePipeline::class)
            ->shouldReceive('run')
            ->once()
            ->andReturn(new RecommendAPlaceExistsCheckData());

        $this->makeRequest($this->paramFactory())->assertNoContent();
    }

    protected function makeRequest(array $params, string $source = 'bar'): TestResponse
    {
        return $this->postJson(
            route('api.v1.eating-out.recommend-a-place.check.store'),
            $params,
            ['x-coeliac-source' => $source],
        );
    }

    protected function paramFactory(mixed $placeName = 'foo', mixed $placeLocation = 'bar'): array
    {
        return [
            'placeName' => $placeName,
            'placeLocation' => $placeLocation,
        ];
    }
}
