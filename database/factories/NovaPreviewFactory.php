<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NovaPreview;
use Illuminate\Support\Str;

class NovaPreviewFactory extends Factory
{
    protected $model = NovaPreview::class;

    public function definition(): array
    {
        return [
            'model' => 'blog',
            'token' => Str::uuid()->toString(),
            'payload' => [
                'title' => $this->faker->sentence,
                'description' => $this->faker->paragraph,
                'body' => $this->faker->paragraph,
                'primary_image_url' => 'https://example.com/image.jpg',
                'social_image_url' => 'https://example.com/social.jpg',
                'show_author' => true,
                'body_images' => [
                    ['file_name' => 'image.jpg', 'url' => 'https://example.com/image.jpg'],
                ],
            ],
        ];
    }
}
