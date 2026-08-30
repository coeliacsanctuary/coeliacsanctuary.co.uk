<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Forms\Components;

use App\Filament\Forms\Components\BodyImageInsertGallery;
use App\Filament\Resources\MainSite\Blogs\Pages\EditBlog;
use App\Models\Blogs\Blog;
use App\Models\Media;
use App\Models\User;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BodyImageInsertGalleryTest extends TestCase
{
    protected Blog $blog;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->actingAs($this->create(User::class));

        $this->blog = $this->create(Blog::class, ['body' => '<p>Start by weighing your flour.</p>']);

        $this->blog->addMedia(UploadedFile::fake()->image('header.jpg'))->toMediaCollection('primary');
    }

    #[Test]
    public function itDefaultsToTheBodyCollection(): void
    {
        $this->assertSame('body', BodyImageInsertGallery::make('images')->getCollection());
    }

    #[Test]
    public function itCanBePointedAtAnotherCollection(): void
    {
        $this->assertSame('gallery', BodyImageInsertGallery::make('images')->collection('gallery')->getCollection());
    }

    #[Test]
    public function itIsNeverDehydratedIntoTheSaveData(): void
    {
        $this->assertFalse(BodyImageInsertGallery::make('images')->isDehydrated());
    }

    #[Test]
    public function itListsEachSavedBodyImage(): void
    {
        $media = $this->addBodyImage('in-body.jpg');

        $items = $this->galleryItems();

        $this->assertCount(1, $items);
        $this->assertFalse($items[0]['pending']);
        $this->assertSame('in-body.jpg', $items[0]['label']);
        $this->assertSame('in-body.jpg', $items[0]['insertSrc']);
        $this->assertSame('body', $items[0]['collection']);
        $this->assertStringContainsString($media->file_name, (string) $items[0]['thumbnail']);
    }

    #[Test]
    public function anImageThatIsNotUsedInTheBodyCanBeDeleted(): void
    {
        $this->addBodyImage('unused.jpg');

        $this->assertTrue($this->galleryItems()[0]['isDeletable']);
    }

    #[Test]
    public function anImageThatIsUsedInTheBodyCannotBeDeleted(): void
    {
        $this->addBodyImage('used.jpg');

        $this->blog->updateQuietly(['body' => '<p>Look</p><img src="used.jpg" />']);

        $this->assertFalse($this->galleryItems()[0]['isDeletable']);
    }

    #[Test]
    public function itSkipsImagesThatHaveSinceBeenDeleted(): void
    {
        $this->addBodyImage('gone.jpg');

        $page = $this->editPage()->set('data.body_images', ['a-uuid-that-has-gone']);

        $this->assertSame([], $this->galleryItemsFor($page));
    }

    #[Test]
    public function itDeletesAnImageThatIsNotUsedInTheBody(): void
    {
        $this->addBodyImage('unused.jpg');

        $this->assertDatabaseCount(Media::class, 2);

        $this->gallery($this->editPage())->deleteBodyImage('unused.jpg', 'body');

        $this->assertDatabaseCount(Media::class, 1);
        $this->assertCount(0, $this->blog->refresh()->getMedia('body'));
    }

    #[Test]
    public function itRefusesToDeleteAnImageThatIsStillUsedInTheBody(): void
    {
        $this->addBodyImage('used.jpg');

        $this->blog->updateQuietly(['body' => '<p>Look</p><img src="used.jpg" />']);

        $this->gallery($this->editPage())->deleteBodyImage('used.jpg', 'body');

        $this->assertCount(1, $this->blog->refresh()->getMedia('body'));
    }

    #[Test]
    #[DataProvider('unsafeFileNames')]
    public function itMatchesTheFileNameTheMediaLibraryWillAssign(string $original, string $sanitised): void
    {
        $gallery = BodyImageInsertGallery::make('images');

        $sanitise = Closure::bind(
            fn (string $fileName): string => $this->sanitiseFileName($fileName),
            $gallery,
            BodyImageInsertGallery::class,
        );

        $this->assertSame($sanitised, $sanitise($original));
    }

    public static function unsafeFileNames(): array
    {
        return [
            'spaces' => ['a loaf of bread.jpg', 'a-loaf-of-bread.jpg'],
            'hashes' => ['bread#1.jpg', 'bread-1.jpg'],
            'forward slashes' => ['bread/1.jpg', 'bread-1.jpg'],
            'back slashes' => ['bread\\1.jpg', 'bread-1.jpg'],
            'nothing to change' => ['bread-1.jpg', 'bread-1.jpg'],
        ];
    }

    protected function addBodyImage(string $fileName): Media
    {
        $this->blog->addMedia(UploadedFile::fake()->image($fileName))->toMediaCollection('body');

        return $this->blog->refresh()->getMedia('body')->last();
    }

    /** @return array<int, array<string, mixed>> */
    protected function galleryItems(): array
    {
        return $this->galleryItemsFor($this->editPage());
    }

    /** @return array<int, array<string, mixed>> */
    protected function galleryItemsFor(Testable $page): array
    {
        return $this->gallery($page)->getGalleryItems();
    }

    protected function gallery(Testable $page): BodyImageInsertGallery
    {
        $body = $page->instance()->form->getFlatComponents(withHidden: true)['body'];

        $gallery = collect($body->getChildSchema('bodyImages')->getComponents(withHidden: true))
            ->first(fn (mixed $component): bool => $component instanceof BodyImageInsertGallery);

        $this->assertInstanceOf(BodyImageInsertGallery::class, $gallery);

        return $gallery;
    }

    protected function editPage(): Testable
    {
        return Livewire::test(EditBlog::class, ['record' => $this->blog->getRouteKey()]);
    }
}
