<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Blogs\Pages;

use App\Filament\Resources\MainSite\Blogs\Pages\CreateBlog;
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

class CreateBlogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->travelTo(Carbon::parse('2026-08-30 12:00:00'));

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itCreatesTheBlog(): void
    {
        $this->assertDatabaseEmpty(Blog::class);

        $this->createBlog()->assertNotified()->assertRedirect();

        $this->assertDatabaseCount(Blog::class, 1);

        $blog = $this->createdBlog();

        $this->assertSame('How To Make Gluten Free Bread', $blog->title);
        $this->assertSame('how-to-make-gluten-free-bread', $blog->slug);
        $this->assertSame('Gluten Free Bread', $blog->short_title);
        $this->assertSame('Everything you need to know.', $blog->description);
        $this->assertSame('bread,baking', $blog->meta_tags);
        $this->assertSame('How to make gluten free bread.', $blog->meta_description);
        $this->assertSame('<p>Start by weighing your flour.</p>', $blog->body);
        $this->assertEquals(1, $blog->show_author);
    }

    #[Test]
    public function itPublishesABlogSetToLive(): void
    {
        $this->createBlog(['status' => 'live', 'publish_at' => Carbon::now()->addDay()]);

        $blog = $this->createdBlog();

        $this->assertTrue($blog->live);
        $this->assertNull($blog->publish_at);
    }

    #[Test]
    public function itSchedulesABlogSetToScheduled(): void
    {
        $this->createBlog(['status' => 'scheduled', 'publish_at' => '2026-09-01 09:00:00']);

        $blog = $this->createdBlog();

        $this->assertFalse($blog->live);
        $this->assertSame('2026-09-01 09:00:00', $blog->publish_at->toDateTimeString());
    }

    #[Test]
    public function itLeavesABlogSetToDraftUnpublished(): void
    {
        $this->createBlog(['status' => 'draft', 'publish_at' => Carbon::now()->addDay()]);

        $blog = $this->createdBlog();

        $this->assertFalse($blog->live);
        $this->assertNull($blog->publish_at);
    }

    #[Test]
    public function itCreatesAndAttachesTheTags(): void
    {
        $this->assertDatabaseEmpty(BlogTag::class);

        $this->createBlog(['tags' => ['baking', 'bread']]);

        $this->assertDatabaseCount(BlogTag::class, 2);
        $this->assertSame(['baking', 'bread'], $this->createdBlog()->tags()->pluck('tag')->all());
    }

    #[Test]
    public function itStoresThePrimaryTag(): void
    {
        $baking = $this->create(BlogTag::class, ['tag' => 'baking']);

        $this->createBlog(['tags' => ['baking'], 'primary_tag_id' => $baking->id]);

        $this->assertSame($baking->id, $this->createdBlog()->primary_tag_id);
    }

    #[Test]
    public function itStoresTheFaqsInOrder(): void
    {
        $undoRepeaterFake = Repeater::fake();

        $this->assertDatabaseEmpty(Faq::class);

        $this->createBlog([
            'faqs' => [
                ['question' => 'Is it gluten free?', 'answer' => 'Yes.'],
                ['question' => 'Can I freeze it?', 'answer' => 'Also yes.'],
            ],
            'faq_display' => 'bottom',
        ]);

        $this->assertDatabaseCount(Faq::class, 2);

        $blog = $this->createdBlog();
        $faqs = $blog->faqs()->get();

        $this->assertSame('Is it gluten free?', $faqs->first()->question);
        $this->assertSame('Yes.', $faqs->first()->answer);
        $this->assertSame('Can I freeze it?', $faqs->last()->question);
        $this->assertSame(1, $faqs->first()->position);
        $this->assertSame(2, $faqs->last()->position);
        $this->assertSame('bottom', $blog->faq_display);

        $undoRepeaterFake();
    }

    #[Test]
    public function itStoresTheHeaderAndSocialImages(): void
    {
        $this->createBlog();

        $blog = $this->createdBlog();

        $this->assertCount(1, $blog->getMedia('primary'));
        $this->assertCount(1, $blog->getMedia('social'));
        $this->assertStringContainsString('header', $blog->getMedia('primary')->first()->file_name);
        $this->assertStringContainsString('social', $blog->getMedia('social')->first()->file_name);
    }

    #[Test]
    public function itStoresTheHeaderImageAltText(): void
    {
        $this->createBlog(['header_image_alt_text' => 'A loaf of gluten free bread']);

        $this->assertSame('A loaf of gluten free bread', $this->createdBlog()->header_image_alt_text);
    }

    protected function createBlog(array $overrides = []): Testable
    {
        return Livewire::test(CreateBlog::class)
            ->fillForm([
                'title' => 'How To Make Gluten Free Bread',
                'slug' => 'how-to-make-gluten-free-bread',
                'short_title' => 'Gluten Free Bread',
                'description' => 'Everything you need to know.',
                'tags' => ['baking'],
                'meta_tags' => 'bread,baking',
                'meta_description' => 'How to make gluten free bread.',
                'body' => '<p>Start by weighing your flour.</p>',
                'show_author' => true,
                'status' => 'live',
                'faqs' => [],
                'header' => [UploadedFile::fake()->image('header.jpg')],
                'social' => [UploadedFile::fake()->image('social.jpg')],
                ...$overrides,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    protected function createdBlog(): Blog
    {
        return Blog::query()->withoutGlobalScopes()->firstOrFail();
    }
}
