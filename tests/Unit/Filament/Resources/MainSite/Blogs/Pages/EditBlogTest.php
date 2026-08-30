<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Blogs\Pages;

use App\Filament\Resources\MainSite\Blogs\BlogResource;
use App\Filament\Resources\MainSite\Blogs\Pages\EditBlog;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use App\Models\Faqs\Faq;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditBlogTest extends TestCase
{
    protected Blog $blog;

    protected BlogTag $tag;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->travelTo(Carbon::parse('2026-08-30 12:00:00'));

        $this->actingAs($this->create(User::class));

        $this->blog = $this->create(Blog::class, [
            'title' => 'How To Make Gluten Free Bread',
            'slug' => 'how-to-make-gluten-free-bread',
            'short_title' => 'Gluten Free Bread',
            'body' => '<p>Start by weighing your flour.</p>',
            'live' => true,
        ]);

        $this->blog->addMedia(UploadedFile::fake()->image('header.jpg'))->toMediaCollection('primary');
        $this->blog->addMedia(UploadedFile::fake()->image('social.jpg'))->toMediaCollection('social');

        $this->tag = $this->create(BlogTag::class, ['tag' => 'baking']);

        $this->blog->tags()->attach($this->tag->id);
    }

    #[Test]
    public function itFillsTheFormFromTheBlog(): void
    {
        $this->editPage()->assertSchemaStateSet([
            'title' => 'How To Make Gluten Free Bread',
            'slug' => 'how-to-make-gluten-free-bread',
            'short_title' => 'Gluten Free Bread',
            'body' => '<p>Start by weighing your flour.</p>',
            'description' => $this->blog->description,
            'meta_tags' => $this->blog->meta_tags,
            'meta_description' => $this->blog->meta_description,
        ]);
    }

    #[Test]
    public function itFillsTheTagsFromTheBlog(): void
    {
        $this->blog->tags()->attach($this->create(BlogTag::class, ['tag' => 'bread'])->id);

        $this->editPage()->assertSchemaComponentStateSet('tags', ['baking', 'bread']);
    }

    #[Test]
    public function itFillsThePrimaryTagFromTheBlog(): void
    {
        $this->blog->update(['primary_tag_id' => $this->tag->id]);

        $this->editPage()->assertSchemaComponentStateSet('primary_tag_id', $this->tag->id);
    }

    #[Test]
    public function itShowsALiveBlogAsLive(): void
    {
        $this->editPage()->assertSchemaComponentStateSet('status', 'live');
    }

    #[Test]
    public function itShowsAScheduledBlogAsScheduled(): void
    {
        $this->blog->update(['live' => false, 'publish_at' => Carbon::now()->addDay()]);

        $this->editPage()->assertSchemaComponentStateSet('status', 'scheduled');
    }

    #[Test]
    public function itFillsTheFaqsFromTheBlog(): void
    {
        $undoRepeaterFake = Repeater::fake();

        $this->build(Faq::class)->on($this->blog)->create(['question' => 'Is it gluten free?', 'answer' => 'Yes.']);

        $faqs = $this->editPage()->instance()->form->getRawState()['faqs'];
        $faq = reset($faqs);

        $this->assertCount(1, $faqs);
        $this->assertSame('Is it gluten free?', $faq['question']);
        $this->assertSame('Yes.', $faq['answer']);

        $undoRepeaterFake();
    }

    #[Test]
    public function itUpdatesTheBlog(): void
    {
        $this->editPage()
            ->fillForm(['title' => 'How To Make Gluten Free Cake'])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        $this->assertSame('How To Make Gluten Free Cake', $this->blog->refresh()->title);
    }

    #[Test]
    public function itUnpublishesABlogMovedBackToDraft(): void
    {
        $this->blog->update(['publish_at' => Carbon::now()]);

        $this->editPage()->fillForm(['status' => 'draft'])->call('save')->assertHasNoFormErrors();

        $this->blog->refresh();

        $this->assertFalse($this->blog->live);
        $this->assertNull($this->blog->publish_at);
    }

    #[Test]
    public function itSchedulesABlogMovedToScheduled(): void
    {
        $this->editPage()
            ->fillForm(['status' => 'scheduled', 'publish_at' => '2026-09-05 08:00:00'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->blog->refresh();

        $this->assertFalse($this->blog->live);
        $this->assertSame('2026-09-05 08:00:00', $this->blog->publish_at->toDateTimeString());
    }

    #[Test]
    public function itAddsAndRemovesFaqs(): void
    {
        $undoRepeaterFake = Repeater::fake();

        $this->build(Faq::class)->on($this->blog)->create(['question' => 'Is it gluten free?', 'answer' => 'Yes.']);

        $this->editPage()
            ->fillForm(['faqs' => [['question' => 'Can I freeze it?', 'answer' => 'Yes.']]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount(Faq::class, 1);
        $this->assertSame('Can I freeze it?', $this->blog->faqs()->first()->question);

        $undoRepeaterFake();
    }

    #[Test]
    public function itRemovesEveryFaq(): void
    {
        $undoRepeaterFake = Repeater::fake();

        $this->build(Faq::class)->on($this->blog)->create();

        $this->editPage()->fillForm(['faqs' => []])->call('save')->assertHasNoFormErrors();

        $this->assertDatabaseEmpty(Faq::class);

        $undoRepeaterFake();
    }

    #[Test]
    public function itAddsTagsWithoutDuplicatingExistingOnes(): void
    {
        $this->editPage()->fillForm(['tags' => ['baking', 'bread']])->call('save')->assertHasNoFormErrors();

        $this->assertDatabaseCount(BlogTag::class, 2);
        $this->assertSame(['baking', 'bread'], $this->blog->tags()->pluck('tag')->all());
    }

    #[Test]
    public function itRemovesTagsWithoutDeletingThem(): void
    {
        $this->blog->tags()->attach($this->create(BlogTag::class, ['tag' => 'bread'])->id);

        $this->editPage()->fillForm(['tags' => ['bread']])->call('save')->assertHasNoFormErrors();

        $this->assertDatabaseCount(BlogTag::class, 2);
        $this->assertSame(['bread'], $this->blog->tags()->pluck('tag')->all());
    }

    #[Test]
    public function itSendsTheUserBackToTheBlogListAfterSaving(): void
    {
        $this->editPage()->call('save')->assertRedirect(BlogResource::getUrl('index'));
    }

    #[Test]
    public function itCanEditABlogThatIsNotLive(): void
    {
        $blog = $this->build(Blog::class)->notLive()->create();

        Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()])->assertOk();
    }

    protected function editPage(): Testable
    {
        return Livewire::test(EditBlog::class, ['record' => $this->blog->getRouteKey()]);
    }
}
