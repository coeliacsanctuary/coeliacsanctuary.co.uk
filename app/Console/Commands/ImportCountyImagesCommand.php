<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EatingOut\EateryCounty;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImportCountyImagesCommand extends Command
{
    protected $signature = 'one-time:coeliac:import-county-images {--test}';

    protected $description = 'Command description';

    public function handle(): void
    {
        $disk = Storage::build([
            'driver' => 's3',
            'region' => 'eu-west-2',
            'bucket' => 'county-image-imports',
        ]);

        EateryCounty::query()
            ->whereNot('slug', 'Nationwide')
            ->get()
            ->lazy()
            ->each(function (EateryCounty $county) use ($disk): void {
                $rawImage = $disk->get($county->county . '.png');

                if ( ! $rawImage) {
                    if ($this->option('test')) {
                        $this->error("Failed to get image for {$county->county}");
                    }

                    return;
                }

                if ($this->option('test')) {
                    $this->info("Got image for {$county->county}");

                    return;
                }

                $image = Image::make($rawImage)
                    ->resize(1408, null, function ($constraint): void {
                        $constraint->aspectRatio();
                    })
                    ->encode('png', quality: 95)
                    ->getEncoded();

                $county
                    ->addMediaFromString($image)
                    ->usingFileName("{$county->slug}.png")
                    ->addCustomHeaders([
                        'ACL' => 'public-read',
                    ])
                    ->toMediaCollection('primary');

                $this->info("Added image for {$county->county}");
            });
    }
}
