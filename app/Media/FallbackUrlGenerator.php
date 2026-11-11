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
            $stream = $this->getFallbackDisk()->readStream($path);

            if ($stream) {
                $this->getDisk()->writeStream($path, $stream);
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
