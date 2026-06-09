<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\EatingOut\LatLng;

use App\DataObjects\EatingOut\LatLng;
use App\Services\EatingOut\LocationSearchService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    #[Test]
    public function itErrorsWithOutATerm(): void
    {
        $this->postJson(route('api.wheretoeat.lat-lng'))->assertJsonValidationErrorFor('term');
    }

    #[Test]
    public function itReturnsTheLatLng(): void
    {
        $this->mock(LocationSearchService::class)
            ->shouldReceive('getLatLng')
            ->andReturn(new LatLng(51, -1))
            ->once();

        $this->postJson(route('api.wheretoeat.lat-lng'), ['term' => 'foo'])
            ->assertJson(['lat' => 51, 'lng' => -1]);
    }
}
