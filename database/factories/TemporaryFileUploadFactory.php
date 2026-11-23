<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TemporaryFileUpload;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TemporaryFileUpload>
 */
class TemporaryFileUploadFactory extends Factory
{
    protected $model = TemporaryFileUpload::class;

    public function definition(): array
    {
        return [
            'filename' => $this->faker->uuid(),
            'path' => '/',
            'source' => 'uploads',
            'filesize' => $this->faker->numberBetween(1, 1000),
            'mime' => 'image/jpg',
            'delete_at' => Carbon::now()->addDay(),
        ];
    }
}
