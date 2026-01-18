<?php

declare(strict_types=1);

namespace App\Services\Static\Map;

use App\Models\GoogleStaticMap;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class GoogleMapService
{
    protected ?GoogleStaticMap $existingRecord = null;

    public function __construct(protected ImageManager $imageManager)
    {
        //
    }

    public function renderMap(string $latLng, array $params = []): Image
    {
        $this->resolveExistingRecord($latLng, $params);

        $image = $this->resolveImage($latLng, $params);

        $this->existingRecord?->increment('hits');

        return $image;
    }

    protected function resolveImage(string $latLng, array $params): Image
    {
        if ($this->hasCachedImage()) {
            /** @var GoogleStaticMap $googleStaticMap */
            $googleStaticMap = $this->existingRecord;

            return $this->imageManager->make(Storage::disk('media')->get("/maps/{$googleStaticMap->uuid}-{$googleStaticMap->parameters}.jpg"));
        }

        return $this->resolveImageFromGoogle($latLng, $params);
    }

    protected function getGoogleMapUrl(string $latLng, array $params): string
    {
        return URL::query('https://maps.googleapis.com/maps/api/staticmap', [
            'center' => $latLng,
            'size' => '600x600',
            'maptype' => 'roadmap',
            'markers' => "color:red|label:|{$latLng}",
            'key' => config('services.google.maps.static'),
            ...$params,
        ]);
    }

    protected function resolveExistingRecord(string $latLng, array $parameters): void
    {
        $this->existingRecord = GoogleStaticMap::query()
            ->where('latlng', $latLng)
            ->where('parameters', md5((string)json_encode($parameters)))
            ->first();
    }

    protected function getImageFromGoogle(string $latLng, array $params): string
    {
        /** @var Response $imageRequest */
        $imageRequest = Http::get($this->getGoogleMapUrl($latLng, $params));

        return $imageRequest->getBody()->getContents();
    }

    protected function hasCachedImage(): bool
    {
        if ( ! $this->existingRecord) {
            return false;
        }

        return $this->existingRecord->last_fetched_at->addDays(30)->isFuture();
    }

    protected function resolveImageFromGoogle(string $latLng, array $params): Image
    {
        $image = $this->getImageFromGoogle($latLng, $params);

        $rawImage = $this->imageManager->make($image);

        $uuid = $this->existingRecord->uuid ?? Str::uuid()->toString();

        /** @var resource $encodedImageBlob */
        $encodedImageBlob = $rawImage->encode('jpg');

        $encodedParams = md5((string)json_encode($params));

        Storage::disk('media')->put("maps/{$uuid}-{$encodedParams}.jpg", $encodedImageBlob);

        $this->existingRecord = GoogleStaticMap::query()->updateOrCreate([
            'latlng' => $latLng,
            'parameters' => md5((string)json_encode($params)),
        ], [
            'uuid' => $uuid,
            'last_fetched_at' => now(),
        ]);

        return $rawImage;
    }
}
