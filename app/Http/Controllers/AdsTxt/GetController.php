<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdsTxt;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class GetController
{
    public function __invoke(): Response
    {
        $content = Storage::disk('system')->get('ads.txt');

        if ($content === null) {
            abort(404);
        }

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
