<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Static\Maps;

use App\Models\GoogleStaticMap;
use App\Services\Static\Map\GoogleMapService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoogleMapServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');
        Http::preventStrayRequests();
    }

    #[Test]
    public function itWillReturnOneFromS3IfARecordExistsInTheDatabaseAndWasCreatedWithinTheLast30Days(): void
    {
        $london = '51.5,-0.1';

        $record = $this->create(GoogleStaticMap::class, [
            'latlng' => $london,
        ]);

        Storage::shouldReceive('disk')
            ->once()
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('get')
            ->withArgs(["/maps/{$record->uuid}.jpg"])
            ->andReturn($this->getFakeImageString())
            ->once();

        app(GoogleMapService::class)->renderMap($london);
    }

    #[Test]
    public function itUpdatesTheHits(): void
    {
        $london = '51.5,-0.1';

        $record = $this->create(GoogleStaticMap::class, [
            'latlng' => $london,
            'hits' => 5,
        ]);

        $this->mock(ImageManager::class)
            ->shouldReceive('make')
            ->andReturn(Image::make($this->getFakeImageString()));

        app(GoogleMapService::class)->renderMap($london);

        $this->assertEquals(6, $record->refresh()->hits);
    }

    #[Test]
    public function itGetsANewImageFromGoogleIfThereIsntOneStored(): void
    {
        $london = '51.5,-0.1';

        URL::shouldReceive('query')
            ->once()
            ->withArgs(function (string $host, array $query) use ($london) {
                $this->assertEquals('https://maps.googleapis.com/maps/api/staticmap', $host);
                $this->assertArrayHasKeys(['center', 'size', 'maptype', 'markers', 'key'], $query);
                $this->assertEquals($london, $query['center']);

                return true;
            })
            ->andReturn('https://foo.bar');

        Http::fake([
            'https://foo.bar' => Http::response($this->getFakeImageString(), headers: [
                'Content-Type' => 'Content-Type: image/jpeg',
            ]),
        ]);

        app(GoogleMapService::class)->renderMap($london);
    }

    #[Test]
    public function itStoresTheImage(): void
    {
        $london = '51.5,-0.1';

        URL::shouldReceive('query')->andReturn('https://foo.bar');

        Http::fake([
            'https://foo.bar' => Http::response($this->getFakeImageString(), headers: [
                'Content-Type' => 'Content-Type: image/jpeg',
            ]),
        ]);

        Storage::shouldReceive('disk')
            ->once()
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('put')
            ->once()
            ->andReturnTrue();

        app(GoogleMapService::class)->renderMap($london);
    }

    #[Test]
    public function itCreatesARecordInTheDatabase(): void
    {
        $this->assertDatabaseEmpty(GoogleStaticMap::class);

        $london = '51.5,-0.1';

        URL::shouldReceive('query')->andReturn('https://foo.bar');

        Http::fake([
            'https://foo.bar' => Http::response($this->getFakeImageString(), headers: [
                'Content-Type' => 'Content-Type: image/jpeg',
            ]),
        ]);

        app(GoogleMapService::class)->renderMap($london);

        $this->assertDatabaseCount(GoogleStaticMap::class, 1);

        $record = GoogleStaticMap::query()->first();

        $this->assertEquals($london, $record->latlng);
        $this->assertTrue($record->last_fetched_at->isSameMinute(now()));
        $this->assertEquals(1, $record->hits);
    }

    #[Test]
    public function itIfTheStoredImageHasExpiredItWillFetchANewOneAndUpdateTheRecord(): void
    {
        $london = '51.5,-0.1';
        $uuid = Str::uuid()->toString();

        $record = $this->create(GoogleStaticMap::class, [
            'uuid' => $uuid,
            'latlng' => $london,
            'hits' => 15,
            'last_fetched_at' => now()->subDays(35),
        ]);

        URL::shouldReceive('query')
            ->once()
            ->withArgs(function (string $host, array $query) use ($london) {
                $this->assertEquals('https://maps.googleapis.com/maps/api/staticmap', $host);
                $this->assertArrayHasKeys(['center', 'size', 'maptype', 'markers', 'key'], $query);
                $this->assertEquals($london, $query['center']);

                return true;
            })
            ->andReturn('https://foo.bar');

        Http::fake([
            'https://foo.bar' => Http::response($this->getFakeImageString(), headers: [
                'Content-Type' => 'Content-Type: image/jpeg',
            ]),
        ]);

        Storage::shouldReceive('disk')
            ->once()
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('put')
            ->withArgs(function (string $path) use ($uuid) {
                $this->assertEquals("maps/{$uuid}.jpg", $path);

                return true;
            })
            ->once()
            ->andReturnTrue();

        $this->assertDatabaseCount(GoogleStaticMap::class, 1);

        app(GoogleMapService::class)->renderMap($london);

        $this->assertDatabaseCount(GoogleStaticMap::class, 1);

        $record->refresh();

        $this->assertEquals($uuid, $record->uuid);
        $this->assertEquals($london, $record->latlng);
        $this->assertTrue($record->last_fetched_at->isSameMinute(now()));
        $this->assertEquals(16, $record->hits);
    }

    protected function getFakeImageString(): string|false
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z/C/HgAGgwJ/lK3Q6wAAAABJRU5ErkJggg==');
    }
}
