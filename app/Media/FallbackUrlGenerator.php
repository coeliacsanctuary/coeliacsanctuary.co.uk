<?php

declare(strict_types=1);

namespace App\Media;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class FallbackUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        $path = $this->getPathRelativeToRoot();

        if ( ! $this->getDisk()->exists($path)) {
            $fallbackDisk = $this->getFallbackDisk();

            if ($fallbackDisk->exists($path)) {
                $this->getDisk()->writeStream($path, $fallbackDisk->readStream($path));
            }
        }

        return parent::getUrl();
    }

    protected function getFallbackDisk(): Filesystem
    {
        $config = config("filesystems.disks.{$this->getDiskName()}");

        $bucket = config('media-library.fallback_prefix') . '-' . Str::after($config['bucket'], '-');

        return Storage::build([...$config, 'bucket' => $bucket]);
    }
}
