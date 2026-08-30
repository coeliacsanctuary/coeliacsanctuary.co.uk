<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Blogs\Actions;

use App\Filament\Resources\MainSite\Blogs\Actions\BlogPreviewAction;
use App\Filament\Resources\MainSite\Blogs\Pages\EditBlog;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use App\Models\Faqs\Faq;
use App\Models\NovaPreview;
use App\Models\User;
use Closure;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogPreviewActionTest extends TestCase
{
    protected Blog $blog;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->actingAs($this->create(User::class));

        $this->blog = $this->create(Blog::class, [
            'title' => 'How To Make Gluten Free Bread',
            'short_title' => 'Gluten Free Bread',
            'description' => 'Everything you need to know.',
            'body' => '<p>Start by weighing your flour.</p>',
        ]);

        $this->blog->addMedia(UploadedFile::fake()->image('header.jpg'))->toMediaCollection('primary');
        $this->blog->addMedia(UploadedFile::fake()->image('social.jpg'))->toMediaCollection('social');

        $this->blog->tags()->attach($this->create(BlogTag::class, ['tag' => 'baking'])->id);
    }

    #[Test]
    public function itIsLabelledAndStyledAsASecondaryPreviewButton(): void
    {
        $this->editPage()
            ->assertActionHasLabel($this->previewAction(), 'Preview')
            ->assertActionHasIcon($this->previewAction(), Heroicon::Eye)
            ->assertActionHasColor($this->previewAction(), 'gray');
    }

    #[Test]
    public function itStoresAPreviewOfTheBlog(): void
    {
        $this->assertDatabaseEmpty(NovaPreview::class);

        $this->editPage()->mountAction($this->previewAction());

        $this->assertDatabaseCount(NovaPreview::class, 1);

        $preview = NovaPreview::query()->firstOrFail();

        $this->assertSame('blog', $preview->model);
        $this->assertSame(36, mb_strlen($preview->token));
    }

    #[Test]
    public function itStoresAFreshPreviewEachTimeItIsOpened(): void
    {
        $this->editPage()->mountAction($this->previewAction());
        $this->editPage()->mountAction($this->previewAction());

        $this->assertDatabaseCount(NovaPreview::class, 2);
        $this->assertCount(2, NovaPreview::query()->distinct()->pluck('token'));
    }

    #[Test]
    public function itPreviewsTheContentCurrentlyInTheForm(): void
    {
        $this->editPage()
            ->fillForm(['title' => 'How To Make Gluten Free Cake', 'body' => '<p>Unsaved edit.</p>'])
            ->mountAction($this->previewAction());

        $payload = $this->payload();

        $this->assertSame('How To Make Gluten Free Cake', $payload['title']);
        $this->assertSame('<p>Unsaved edit.</p>', $payload['body']);
        $this->assertSame('Gluten Free Bread', $payload['short_title']);
        $this->assertSame('Everything you need to know.', $payload['description']);
        $this->assertSame(['baking'], $payload['tags']);
        $this->assertTrue($payload['show_author']);
    }

    #[Test]
    public function itPreviewsTheSavedHeaderImage(): void
    {
        $this->editPage()->mountAction($this->previewAction());

        $this->assertStringContainsString('header', (string) $this->payload()['primary_image_url']);
    }

    #[Test]
    public function itPreviewsTheSavedSocialImage(): void
    {
        $this->editPage()->mountAction($this->previewAction());

        $this->assertStringContainsString('social', (string) $this->payload()['social_image_url']);
    }

    #[Test]
    public function itPreviewsTheHeaderImageAltText(): void
    {
        $this->editPage()
            ->fillForm(['header_image_alt_text' => 'A loaf of gluten free bread'])
            ->mountAction($this->previewAction());

        $this->assertSame('A loaf of gluten free bread', $this->payload()['header_image_alt_text']);
    }

    #[Test]
    #[DataProvider('missingContent')]
    public function itRefusesToPreviewIncompleteContent(string $field): void
    {
        $this->editPage()
            ->fillForm([$field => null])
            ->mountAction($this->previewAction());

        $this->assertDatabaseEmpty(NovaPreview::class);
    }

    public static function missingContent(): array
    {
        return [
            'no title' => ['title'],
            'no description' => ['description'],
            'no body' => ['body'],
        ];
    }

    #[Test]
    public function itRefusesToPreviewABlogWithNoHeaderImage(): void
    {
        $blog = $this->create(Blog::class);

        Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()])
            ->mountAction($this->previewAction());

        $this->assertDatabaseEmpty(NovaPreview::class);
    }

    #[Test]
    #[DataProvider('missingContentMessages')]
    public function itExplainsWhatIsStoppingThePreview(string $key, string $message): void
    {
        $payload = ['title' => 'A title', 'description' => 'A description', 'body' => 'A body', 'primary_image_url' => 'https://example.com/i.jpg'];

        $payload[$key] = null;

        $this->assertSame([$message], $this->callProtected('validatePayload', $payload));
    }

    public static function missingContentMessages(): array
    {
        return [
            'title' => ['title', 'A title is required to preview.'],
            'description' => ['description', 'A description is required to preview.'],
            'body' => ['body', 'A body is required to preview.'],
            'header image' => ['primary_image_url', 'A header image is required to preview.'],
        ];
    }

    #[Test]
    public function itExplainsEveryReasonThePreviewIsBlocked(): void
    {
        $this->assertSame([
            'A title is required to preview.',
            'A description is required to preview.',
            'A body is required to preview.',
            'A header image is required to preview.',
        ], $this->callProtected('validatePayload', []));
    }

    #[Test]
    public function itHasNothingToReportWhenThePreviewIsComplete(): void
    {
        $this->assertSame([], $this->callProtected('validatePayload', [
            'title' => 'A title',
            'description' => 'A description',
            'body' => 'A body',
            'primary_image_url' => 'https://example.com/i.jpg',
        ]));
    }

    #[Test]
    public function itPreviewsThePrimaryTagByNameRatherThanId(): void
    {
        $tag = BlogTag::query()->firstOrFail();

        $this->editPage()->fillForm(['primary_tag_id' => $tag->id])->mountAction($this->previewAction());

        $this->assertSame('baking', $this->payload()['primary_tag_id']);
    }

    #[Test]
    public function itPreviewsNoPrimaryTagWhenNoneIsChosen(): void
    {
        $this->editPage()->fillForm(['primary_tag_id' => null])->mountAction($this->previewAction());

        $this->assertNull($this->payload()['primary_tag_id']);
    }

    #[Test]
    public function itPreviewsNoPrimaryTagWhenTheTagHasGone(): void
    {
        $this->assertNull($this->callProtected('primaryTagName', 99999));
    }

    #[Test]
    public function itPreviewsTheFaqs(): void
    {
        $this->build(Faq::class)->on($this->blog)->create(['question' => 'Is it gluten free?', 'answer' => 'Yes.']);

        $this->editPage()->fillForm(['faq_display' => 'top'])->mountAction($this->previewAction());

        $payload = $this->payload();

        $this->assertCount(1, $payload['faqs']);
        $this->assertSame('Is it gluten free?', $payload['faqs'][0]['question']);
        $this->assertSame('Yes.', $payload['faqs'][0]['answer']);
        $this->assertSame('top', $payload['faq_display']);
    }

    #[Test]
    public function itPreviewsNoFaqsWhenThereAreNone(): void
    {
        $this->editPage()->mountAction($this->previewAction());

        $this->assertSame([], $this->payload()['faqs']);
    }

    #[Test]
    public function itLeavesOutFaqsWithNoQuestionYet(): void
    {
        $faqs = $this->callProtected('faqs', [
            ['question' => 'Is it gluten free?', 'answer' => 'Yes.'],
            ['question' => null, 'answer' => 'An orphaned answer.'],
        ]);

        $this->assertSame([['question' => 'Is it gluten free?', 'answer' => 'Yes.']], $faqs);
    }

    #[Test]
    public function itPreviewsAnFaqThatHasNoAnswerYet(): void
    {
        $this->assertSame(
            [['question' => 'Is it gluten free?', 'answer' => null]],
            $this->callProtected('faqs', [['question' => 'Is it gluten free?']])
        );
    }

    #[Test]
    public function itPreviewsTheSavedBodyImages(): void
    {
        $this->blog->addMedia(UploadedFile::fake()->image('in-body.jpg'))->toMediaCollection('body');

        $this->editPage()->mountAction($this->previewAction());

        $bodyImages = $this->payload()['body_images'];

        $this->assertCount(1, $bodyImages);
        $this->assertSame('in-body.jpg', $bodyImages[0]['file_name']);
        $this->assertStringContainsString('in-body', (string) $bodyImages[0]['url']);
    }

    #[Test]
    public function itLeavesOutBodyImagesThatNoLongerExist(): void
    {
        $this->assertSame([], $this->callProtected('bodyImages', ['a-uuid-that-has-gone'], $this->blog));
    }

    #[Test]
    public function itPreviewsWithoutTheAuthorBlockWhenItIsTurnedOff(): void
    {
        $this->editPage()->fillForm(['show_author' => false])->mountAction($this->previewAction());

        $this->assertFalse($this->payload()['show_author']);
    }

    #[Test]
    #[DataProvider('unsafeFileNames')]
    public function itMatchesTheFileNameTheMediaLibraryWillAssign(string $original, string $sanitised): void
    {
        $this->assertSame($sanitised, $this->callProtected('sanitiseFileName', $original));
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

    protected function callProtected(string $method, mixed ...$arguments): mixed
    {
        $call = Closure::bind(
            fn (string $method, array $arguments): mixed => BlogPreviewAction::{$method}(...$arguments),
            null,
            BlogPreviewAction::class,
        );

        return $call($method, $arguments);
    }

    protected function previewAction(): TestAction
    {
        return TestAction::make('preview')->schemaComponent();
    }

    protected function editPage(): Testable
    {
        return Livewire::test(EditBlog::class, ['record' => $this->blog->getRouteKey()]);
    }

    /** @return array<string, mixed> */
    protected function payload(): array
    {
        return NovaPreview::query()->latest('id')->firstOrFail()->payload;
    }
}
