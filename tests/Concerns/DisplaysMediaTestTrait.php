<?php

declare(strict_types=1);

namespace Tests\Concerns;

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Tests\TestCase;

/** @mixin TestCase  */
trait DisplaysMediaTestTrait
{
    protected const SOCIAL_IMAGE = 'social.jpg';

    protected const PRIMARY_IMAGE = 'primary.jpg';

    protected const FIRST_IMAGE = 'article1.jpg';

    protected const BODY_IMAGE = 'article2.jpg';

    protected Model $item;

    /** @param  callable(): HasMedia  $item  */
    protected function setUpDisplaysMediaTest(callable $item): void
    {
        Storage::fake('media');

        $item = $item();

        $item->addMedia(UploadedFile::fake()->image(self::SOCIAL_IMAGE))->toMediaCollection('social');
        $item->addMedia(UploadedFile::fake()->image(self::PRIMARY_IMAGE))->toMediaCollection('primary');
        $item->addMedia(UploadedFile::fake()->image(self::FIRST_IMAGE))->toMediaCollection();
        $item->addMedia(UploadedFile::fake()->image(self::BODY_IMAGE))->toMediaCollection();

        $this->item = $item;
    }

    #[Test]
    public function itCanGetTheFirstImage(): void
    {
        $firstImage = $this->item->first_image;

        $this->assertNotNull($firstImage);
        $this->assertStringContainsString(self::FIRST_IMAGE, $firstImage);
    }

    #[Test]
    public function itCanGetThePrimaryImage(): void
    {
        $primaryImage = $this->item->main_image;

        $this->assertNotNull($primaryImage);
        $this->assertStringContainsString(self::PRIMARY_IMAGE, $primaryImage);
    }

    #[Test]
    public function itCanGetTheSocialImage(): void
    {
        $socialImage = $this->item->social_image;

        $this->assertNotNull($socialImage);
        $this->assertStringContainsString(self::SOCIAL_IMAGE, $socialImage);
    }
}
