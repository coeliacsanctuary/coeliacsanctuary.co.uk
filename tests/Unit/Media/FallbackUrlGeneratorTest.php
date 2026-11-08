<?php

declare(strict_types=1);

namespace Tests\Unit\Media;

use PHPUnit\Framework\Attributes\Test;
use App\Media\FallbackUrlGenerator;
use App\Models\Blogs\Blog;
use App\Models\Media;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;
use Tests\TestCase;

class FallbackUrlGeneratorTest extends TestCase
{
    protected Media $media;

    protected string $path;

    protected Filesystem $fallbackDisk;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('filesystems.disks.media.bucket', 'local-coeliac-media');
        config()->set('media-library.fallback_prefix', 'prod');
        config()->set('media-library.url_generator', FallbackUrlGenerator::class);

        Storage::fake('media');

        $this->create(Blog::class)->addMedia(UploadedFile::fake()->image('foo.jpg'))->toMediaCollection();

        $this->media = Media::query()->first();
        $this->path = $this->media->getPathRelativeToRoot();

        $this->fallbackDisk = Storage::fake('fallback');

        Storage::swap(
            Mockery::mock(Storage::getFacadeRoot())
                ->shouldReceive('build')
                ->with(Mockery::on(fn (array $config) => $config['bucket'] === 'prod-coeliac-media'))
                ->andReturn($this->fallbackDisk)
                ->getMock()
        );
    }

    #[Test]
    public function itCopiesTheFileFromTheFallbackBucketWhenMissing(): void
    {
        Storage::disk('media')->delete($this->path);
        $this->fallbackDisk->put($this->path, 'fallback contents');

        $this->media->getUrl();

        $this->assertSame('fallback contents', Storage::disk('media')->get($this->path));
    }

    #[Test]
    public function itDoesNotTouchTheFallbackBucketWhenTheFileExists(): void
    {
        Storage::disk('media')->put($this->path, 'local contents');
        $this->fallbackDisk->put($this->path, 'fallback contents');

        $this->media->getUrl();

        $this->assertSame('local contents', Storage::disk('media')->get($this->path));
    }

    #[Test]
    public function itReturnsTheSameUrlAsTheDefaultGenerator(): void
    {
        $url = $this->media->getUrl();

        config()->set('media-library.url_generator', DefaultUrlGenerator::class);

        $this->assertSame($this->media->getUrl(), $url);
    }

    #[Test]
    public function itReturnsTheUrlWhenTheFileIsMissingFromBothBuckets(): void
    {
        Storage::disk('media')->delete($this->path);

        $url = $this->media->getUrl();

        Storage::disk('media')->assertMissing($this->path);
        $this->assertStringEndsWith($this->path, $url);
    }
}
