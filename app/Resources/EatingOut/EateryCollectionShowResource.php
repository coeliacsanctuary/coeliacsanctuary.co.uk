<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\EateryCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/** @mixin EateryCollection */
class EateryCollectionShowResource extends JsonResource
{
    /** @return array{id: number, title: string|Stringable, image: string, published: string, updated: string, description: string, body: string|Stringable} */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'title' => Str::of($this->title)->replace('&quot;', '"'),
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'published' => $this->published,
            'updated' => $this->lastUpdated,
            'description' => $this->description,
            'body' => Str::of($this->body)
                ->replace('&quot;', '"')
                ->markdown([
                    'renderer' => [
                        'soft_break' => '<br />',
                    ],
                ]),
        ];
    }
}
