<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Static\Map;

use App\Services\Static\Map\GoogleMapService;
use Illuminate\Support\Facades\Image;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    #[Test]
    public function itReturns404IfTheLatLngIsIncorrectlyFormatted(): void
    {
        $this->get(route('static.map', ['latlng' => 'foo']))->assertNotFound();
    }

    #[Test]
    public function itCallsTheGoogleMapService(): void
    {
        $london = '51.5,-0.1';

        $this->mock(GoogleMapService::class)
            ->shouldReceive('renderMap')
            ->withArgs([$london, []])
            ->once()
            ->andReturn(Image::fromBytes($this->getFakeImageString()));

        $this->get(route('static.map', ['latlng' => $london]))->assertOk();
    }

    #[Test]
    public function itPassesAdditionalParamsToGoogleMaps(): void
    {
        $london = '51.5,-0.1';
        $params = ['foo' => 'bar'];

        $this->mock(GoogleMapService::class)
            ->shouldReceive('renderMap')
            ->withArgs([$london, $params])
            ->once()
            ->andReturn(Image::fromBytes($this->getFakeImageString()));

        $this->get(route('static.map', ['latlng' => $london, 'params' => json_encode($params)]))->assertOk();
    }

    #[Test]
    public function itCallsTheResponseOnInterventionImageUsingJpg(): void
    {
        $london = '51.5,-0.1';

        $this->mock(GoogleMapService::class)
            ->shouldReceive('renderMap')
            ->andReturn(Image::fromBytes($this->getFakeImageString()));

        $this->get(route('static.map', ['latlng' => $london]))->assertHeader('Content-Type', 'image/jpeg');
    }

    protected function getFakeImageString(): string|false
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z/C/HgAGgwJ/lK3Q6wAAAABJRU5ErkJggg==');
    }
}
