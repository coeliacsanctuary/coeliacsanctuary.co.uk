<?php

declare(strict_types=1);

namespace App\Services\Static\Map;

use App\Models\GoogleStaticMap;
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

    public function renderMap(string $latLng): Image
    {
        $this->resolveExistingRecord($latLng);

        $image = $this->resolveImage($latLng);

        $this->existingRecord?->increment('hits');

        return $image;
    }

    protected function resolveImage(string $latLng): Image
    {
        if ($this->hasCachedImage()) {
            return $this->imageManager->make(Storage::disk('media')->get("/maps/{$this->existingRecord->uuid}.jpg"));
        }

        return $this->resolveImageFromGoogle($latLng);
    }

    protected function getGoogleMapUrl(string $latLng): string
    {
        return URL::query('https://maps.googleapis.com/maps/api/staticmap', [
            'center' => $latLng,
            'size' => '600x600',
            'maptype' => 'roadmap',
            'markers' => "color:red|label:|{$latLng}",
            'key' => config('services.google.maps.static'),
        ]);
    }

    protected function resolveExistingRecord(string $latLng): void
    {
        $this->existingRecord = GoogleStaticMap::query()->where('latlng', $latLng)->first();
    }

    protected function getImageFromGoogle(string $latLng): string
    {
        $imageRequest = Http::get($this->getGoogleMapUrl($latLng));

        return $imageRequest->getBody()->getContents();
    }

    protected function hasCachedImage(): bool
    {
        if ( ! $this->existingRecord) {
            return false;
        }

        return $this->existingRecord->last_fetched_at->addDays(30)->isFuture();
    }

    protected function resolveImageFromGoogle(string $latLng): Image
    {
        $image = $this->getImageFromGoogle($latLng);

        $rawImage = $this->imageManager->make($image);

        $uuid = $this->existingRecord->uuid ?? Str::uuid()->toString();

        Storage::disk('media')->put("maps/{$uuid}.jpg", $rawImage->encode('jpg'));

        $this->existingRecord = GoogleStaticMap::query()->updateOrCreate([
            'latlng' => $latLng,
        ], [
            'uuid' => $uuid,
            'last_fetched_at' => now(),
        ]);

        return $rawImage;
    }
}
