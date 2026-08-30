<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Forms\Components;

use App\Filament\Forms\Components\Body;
use App\Filament\Forms\Components\BodyImageInsertGallery;
use App\Filament\Resources\MainSite\Blogs\Pages\EditBlog;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use App\Models\User;
use App\Rules\ValidArticleHtmlRule;
use Filament\Facades\Filament;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Events\RecordSaved;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class BodyTest extends TestCase
{
    #[Test]
    public function itDefaultsToTwentyFiveRows(): void
    {
        $this->assertSame(25, Body::make('body')->getRows());
    }

    #[Test]
    public function itAllowsOverridingRows(): void
    {
        $this->assertSame(15, Body::make('body')->rows(15)->getRows());
    }

    #[Test]
    public function itHasTheToolbarEnabledByDefault(): void
    {
        $this->assertTrue(Body::make('body')->hasToolbar());
    }

    #[Test]
    public function itCanDisableTheToolbar(): void
    {
        $this->assertFalse(Body::make('body')->toolbar(false)->hasToolbar());
    }

    #[Test]
    public function itCanReEnableTheToolbar(): void
    {
        $this->assertTrue(Body::make('body')->toolbar(false)->toolbar()->hasToolbar());
    }

    #[Test]
    public function itUsesTheCustomBodyView(): void
    {
        $this->assertSame('filament.forms.components.body', Body::make('body')->getView());
    }

    #[Test]
    public function itDoesNotAddTheValidationRuleByDefault(): void
    {
        $rules = collect(Body::make('body')->getValidationRules());

        $this->assertFalse($rules->contains(fn ($rule) => $rule instanceof ValidArticleHtmlRule));
    }

    #[Test]
    public function itAddsTheValidArticleHtmlRuleWhenValidHtmlIsCalled(): void
    {
        $rules = collect(Body::make('body')->validHtml()->getValidationRules());

        $this->assertTrue($rules->contains(fn ($rule) => $rule instanceof ValidArticleHtmlRule));
    }

    #[Test]
    public function itHasNoImagesByDefault(): void
    {
        $this->assertFalse(Body::make('body')->hasImages());
    }

    #[Test]
    public function itCanBeGivenAnImageGallery(): void
    {
        $this->assertTrue(Body::make('body')->images()->hasImages());
    }

    #[Test]
    public function theImagesUseTheBodyCollectionByDefault(): void
    {
        $this->assertSame('body', Body::make('body')->images()->getImagesCollection());
    }

    #[Test]
    public function theImagesCanUseAnotherCollection(): void
    {
        $this->assertSame('gallery', Body::make('body')->images('gallery')->getImagesCollection());
    }

    #[Test]
    public function itAddsAnUploaderAndAGalleryBesideTheField(): void
    {
        [$blog] = $this->blogWithABodyImage();

        $body = $this->editPage($blog)
            ->instance()
            ->form
            ->getFlatComponents(withHidden: true)['body'];

        $children = $body->getChildSchema('bodyImages')->getComponents(withHidden: true);

        $upload = collect($children)->first(fn (mixed $child): bool => $child instanceof SpatieMediaLibraryFileUpload);
        $gallery = collect($children)->first(fn (mixed $child): bool => $child instanceof BodyImageInsertGallery);

        $this->assertSame('body_images', $upload->getName());
        $this->assertSame('body', $upload->getCollection());
        $this->assertTrue($upload->isMultiple());

        $this->assertSame('body_images_gallery', $gallery->getName());
        $this->assertSame('body', $gallery->getCollection());
    }

    #[Test]
    public function itShowsImageFileNamesRatherThanUrlsWhileEditing(): void
    {
        [$blog, $media] = $this->blogWithABodyImage();

        $blog->updateQuietly(['body' => '<p>Look</p><img src="' . $media->getUrl() . '" />']);

        $state = $this->editPage($blog)
            ->instance()
            ->form
            ->getRawState()['body'];

        $this->assertStringContainsString($media->file_name, $state);
        $this->assertStringNotContainsString($media->getUrl(), $state);
    }

    #[Test]
    public function itPutsTheImageUrlsBackIntoTheBodyOnSave(): void
    {
        [$blog, $media] = $this->blogWithABodyImage();

        $this->editPage($blog)
            ->fillForm(['body' => '<p>Look</p><img src="' . $media->file_name . '" />'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('<p>Look</p><img src="' . $media->getUrl() . '" />', $blog->refresh()->body);
    }

    #[Test]
    public function itOnlyRewritesTheImageUrlOnceHoweverManyTimesTheFormIsBuilt(): void
    {
        [$blog, $media] = $this->blogWithABodyImage();

        $this->editPage($blog);
        $this->editPage($blog);

        $this->editPage($blog)
            ->fillForm(['body' => '<p>Look</p><img src="' . $media->file_name . '" />'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(1, mb_substr_count($blog->refresh()->body, $media->getUrl()));
    }

    #[Test]
    public function itLeavesABlogWithAnEmptyBodyAloneOnSave(): void
    {
        [$blog] = $this->blogWithABodyImage();

        $this->editPage($blog);

        $blog->updateQuietly(['body' => '']);

        RecordSaved::dispatch($blog, [], $this->editPage($blog)->instance());

        $this->assertSame('', $blog->refresh()->body);
    }

    protected function editPage(Blog $blog): Testable
    {
        return Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()]);
    }

    /** @return array{0: Blog, 1: Media} */
    protected function blogWithABodyImage(): array
    {
        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->actingAs($this->create(User::class));

        $blog = $this->create(Blog::class);

        $blog->addMedia(UploadedFile::fake()->image('header.jpg'))->toMediaCollection('primary');
        $blog->addMedia(UploadedFile::fake()->image('social.jpg'))->toMediaCollection('social');
        $blog->addMedia(UploadedFile::fake()->image('in-body.jpg'))->toMediaCollection('body');

        $blog->tags()->attach($this->create(BlogTag::class, ['tag' => 'baking'])->id);

        return [$blog, $blog->refresh()->getMedia('body')->first()];
    }
}
