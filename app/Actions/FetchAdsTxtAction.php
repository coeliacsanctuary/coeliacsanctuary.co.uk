<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FetchAdsTxtAction
{
    public function handle(): bool
    {
        /** @var Response $response */
        $response = Http::get('https://adstxt.mediavine.com/sites/8b9074d5-fb27-4c85-a1ca-f79af2899f37/ads.txt');

        if ( ! $response->successful()) {
            return false;
        }

        Storage::disk('system')->put('ads.txt', $response->body());

        return true;
    }
}
