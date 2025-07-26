<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EatingOut\EateryTown;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImportLondonBoroughImagesCommand extends Command
{
    protected $signature = 'one-time:coeliac:import-borough-images {--test}';

    public function handle(): void
    {
        $disk = Storage::build([
            'driver' => 's3',
            'region' => 'eu-west-2',
            'key' => config('filesystems.disks.media.key'),
            'secret' => config('filesystems.disks.media.secret'),
            'bucket' => 'borough-image-imports',
        ]);

        EateryTown::query()
            ->where('county_id', 48)
            ->lazy()
            ->each(function (EateryTown $town) use ($disk): void {
                $rawImage = $disk->get($town->town . '.png');

                if ( ! $rawImage) {
                    if ($this->option('test')) {
                        $this->error("Failed to get image for {$town->town}");
                    }

                    return;
                }

                if ($this->option('test')) {
                    $this->info("Got image for {$town->town}");

                    return;
                }

                $image = Image::make($rawImage)
                    ->resize(1408, null, function ($constraint): void {
                        $constraint->aspectRatio();
                    })
                    ->encode('png', quality: 95)
                    ->getEncoded();

                $town
                    ->addMediaFromString($image)
                    ->usingFileName("{$town->slug}.png")
                    ->addCustomHeaders([
                        'ACL' => 'public-read',
                    ])
                    ->toMediaCollection('primary');

                $this->info("Added image for {$town->town}");
            });
    }
}
