<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Blogs\Schemas;

use App\Filament\Resources\MainSite\Blogs\Pages\CreateBlog;
use App\Filament\Resources\MainSite\Blogs\Pages\EditBlog;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itAcceptsACompleteBlog(): void
    {
        $this->fillCreateForm()->call('create')->assertHasNoFormErrors();
    }

    #[Test]
    #[DataProvider('invalidBlogs')]
    public function itValidatesTheBlog(array $overrides, array $errors): void
    {
        $this->fillCreateForm($overrides)
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    }

    public static function invalidBlogs(): array
    {
        return [
            '`title` is required' => [['title' => null], ['title' => 'required']],
            '`title` is max 200 characters' => [['title' => Str::random(201)], ['title' => 'max']],
            '`slug` is required' => [['slug' => null], ['slug' => 'required']],
            '`slug` is max 200 characters' => [['slug' => Str::repeat('a', 201)], ['slug' => 'max']],
            '`slug` rejects capitals' => [['slug' => 'Gluten-Free'], ['slug' => 'regex']],
            '`slug` rejects spaces' => [['slug' => 'gluten free'], ['slug' => 'regex']],
            '`slug` rejects underscores' => [['slug' => 'gluten_free'], ['slug' => 'regex']],
            '`slug` rejects full stops' => [['slug' => 'gluten.free'], ['slug' => 'regex']],
            '`description` is required' => [['description' => null], ['description' => 'required']],
            '`tags` is required' => [['tags' => []], ['tags' => 'required']],
            '`meta_tags` is required' => [['meta_tags' => null], ['meta_tags' => 'required']],
            '`meta_description` is required' => [['meta_description' => null], ['meta_description' => 'required']],
            '`body` is required' => [['body' => null], ['body' => 'required']],
            '`short_title` is max 100 characters' => [['short_title' => Str::random(101)], ['short_title' => 'max']],
            '`header` is required' => [['header' => []], ['header' => 'required']],
            '`social` is required' => [['social' => []], ['social' => 'required']],
        ];
    }

    #[Test]
    public function itRejectsASlugThatIsAlreadyTaken(): void
    {
        $this->create(Blog::class, ['slug' => 'how-to-make-gluten-free-bread']);

        $this->fillCreateForm(['slug' => 'how-to-make-gluten-free-bread'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    #[Test]
    public function itAcceptsALowercaseHyphenatedSlug(): void
    {
        $this->fillCreateForm(['slug' => 'gluten-free-bread-2026'])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    public function itRejectsARawIframeInTheBody(): void
    {
        $this->fillCreateForm(['body' => '<p>Watch this</p><iframe src="https://example.com"></iframe>'])
            ->call('create')
            ->assertHasFormErrors(['body']);
    }

    #[Test]
    public function itRejectsMismatchedTagCasingInTheBody(): void
    {
        $this->fillCreateForm(['body' => '<p>Some text</P>'])
            ->call('create')
            ->assertHasFormErrors(['body']);
    }

    #[Test]
    public function itAcceptsTheCustomArticleTagsInTheBody(): void
    {
        $this->fillCreateForm(['body' => '<article-image src="foo.jpg"></article-image><p>Text</p>'])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    public function itRequiresBothFieldsOnAnFaqThatHasBeenAdded(): void
    {
        $undoRepeaterFake = Repeater::fake();

        $this->fillCreateForm(['faqs' => [['question' => null, 'answer' => null]]])
            ->call('create')
            ->assertHasFormErrors([
                'faqs.0.question' => 'required',
                'faqs.0.answer' => 'required',
            ]);

        $undoRepeaterFake();
    }

    #[Test]
    public function itStartsWithNoFaqRows(): void
    {
        $undoRepeaterFake = Repeater::fake();

        Livewire::test(CreateBlog::class)->assertSchemaStateSet(['faqs' => []]);

        $undoRepeaterFake();
    }

    #[Test]
    public function itRequiresAPublishDateWhenScheduling(): void
    {
        $this->fillCreateForm(['status' => 'scheduled', 'publish_at' => null])
            ->call('create')
            ->assertHasFormErrors(['publish_at' => 'required']);
    }

    #[Test]
    public function itDoesNotRequireAPublishDateOtherwise(): void
    {
        $this->fillCreateForm(['status' => 'draft', 'publish_at' => null])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    public function itSlugsTheTitleWhenTheSlugIsEmpty(): void
    {
        Livewire::test(CreateBlog::class)
            ->fillForm(['title' => 'How To Make Gluten Free Bread'])
            ->assertSchemaStateSet(['slug' => 'how-to-make-gluten-free-bread']);
    }

    #[Test]
    public function itFollowsTheTitleWhileTheSlugStillMatchesIt(): void
    {
        Livewire::test(CreateBlog::class)
            ->fillForm(['title' => 'How To Make Gluten Free Bread'])
            ->fillForm(['title' => 'How To Make Gluten Free Cake'])
            ->assertSchemaStateSet(['slug' => 'how-to-make-gluten-free-cake']);
    }

    #[Test]
    public function itLeavesASlugThatHasBeenEditedByHand(): void
    {
        Livewire::test(CreateBlog::class)
            ->fillForm(['title' => 'How To Make Gluten Free Bread'])
            ->fillForm(['slug' => 'my-own-slug'])
            ->fillForm(['title' => 'How To Make Gluten Free Cake'])
            ->assertSchemaStateSet(['slug' => 'my-own-slug']);
    }

    #[Test]
    public function theSlugCanBeSetWhenCreating(): void
    {
        Livewire::test(CreateBlog::class)
            ->assertSchemaComponentExists('slug', checkComponentUsing: fn (TextInput $f): bool => ! $f->isDisabled());
    }

    #[Test]
    public function theSlugCannotBeChangedWhenEditing(): void
    {
        $blog = $this->create(Blog::class);

        Livewire::test(EditBlog::class, ['record' => $blog->getRouteKey()])
            ->assertSchemaComponentExists('slug', checkComponentUsing: fn (TextInput $f): bool => $f->isDisabled());
    }

    #[Test]
    public function thePrimaryTagOnlyOffersTagsOnTheBlog(): void
    {
        $baking = $this->create(BlogTag::class, ['tag' => 'baking']);
        $this->create(BlogTag::class, ['tag' => 'unrelated']);

        Livewire::test(CreateBlog::class)
            ->fillForm(['tags' => ['baking']])
            ->assertSchemaComponentExists(
                'primary_tag_id',
                checkComponentUsing: fn (Select $f): bool => array_keys($f->getOptions()) === [$baking->id],
            );
    }

    #[Test]
    public function thePrimaryTagLabelsEachTagWithItsBlogCount(): void
    {
        $baking = $this->create(BlogTag::class, ['tag' => 'baking']);
        $this->create(Blog::class, 2)->each(fn (Blog $blog) => $blog->tags()->attach($baking->id));

        Livewire::test(CreateBlog::class)
            ->fillForm(['tags' => ['baking']])
            ->assertSchemaComponentExists(
                'primary_tag_id',
                checkComponentUsing: fn (Select $f): bool => $f->getOptions()[$baking->id] === 'baking - (2 blogs)',
            );
    }

    #[Test]
    public function thePrimaryTagIsDisabledUntilTagsAreAdded(): void
    {
        Livewire::test(CreateBlog::class)
            ->assertSchemaComponentExists('primary_tag_id', checkComponentUsing: fn (Select $f): bool => $f->isDisabled())
            ->fillForm(['tags' => ['baking']])
            ->assertSchemaComponentExists('primary_tag_id', checkComponentUsing: fn (Select $f): bool => ! $f->isDisabled());
    }

    #[Test]
    public function thePrimaryTagExplainsWhyItIsDisabled(): void
    {
        Livewire::test(CreateBlog::class)->assertSchemaComponentExists(
            'primary_tag_id',
            checkComponentUsing: fn (Select $f): bool => $this->helperText($f) === 'Please add tags before selecting a primary tag.',
        );
    }

    #[Test]
    public function thePrimaryTagExplainsWhatItIsForOnceTagsAreAdded(): void
    {
        Livewire::test(CreateBlog::class)
            ->fillForm(['tags' => ['baking']])
            ->assertSchemaComponentExists(
                'primary_tag_id',
                checkComponentUsing: fn (Select $f): bool => str_starts_with(
                    $this->helperText($f),
                    'If set, the primary tag will be used to find related blogs',
                ),
            );
    }

    #[Test]
    public function itClearsThePrimaryTagWhenThatTagIsRemoved(): void
    {
        $baking = $this->create(BlogTag::class, ['tag' => 'baking']);
        $this->create(BlogTag::class, ['tag' => 'bread']);

        Livewire::test(CreateBlog::class)
            ->fillForm(['tags' => ['baking', 'bread']])
            ->fillForm(['primary_tag_id' => $baking->id])
            ->fillForm(['tags' => ['bread']])
            ->assertSchemaStateSet(['primary_tag_id' => null]);
    }

    #[Test]
    public function itKeepsThePrimaryTagWhenAnotherTagIsRemoved(): void
    {
        $baking = $this->create(BlogTag::class, ['tag' => 'baking']);
        $this->create(BlogTag::class, ['tag' => 'bread']);

        Livewire::test(CreateBlog::class)
            ->fillForm(['tags' => ['baking', 'bread']])
            ->fillForm(['primary_tag_id' => $baking->id])
            ->fillForm(['tags' => ['baking']])
            ->assertSchemaStateSet(['primary_tag_id' => $baking->id]);
    }

    #[Test]
    public function itShowsTheAuthorBlockByDefault(): void
    {
        Livewire::test(CreateBlog::class)->assertSchemaStateSet(['show_author' => true]);
    }

    #[Test]
    public function theAuthorBlockCanBeTurnedOff(): void
    {
        Livewire::test(CreateBlog::class)
            ->assertSchemaComponentExists('show_author', checkComponentUsing: fn (Toggle $f): bool => $f->getLabel() === 'Show Author');
    }

    #[Test]
    public function theFaqsCanBePlacedAboveOrBelowTheContent(): void
    {
        Livewire::test(CreateBlog::class)->assertSchemaComponentExists(
            'faq_display',
            checkComponentUsing: fn (Select $f): bool => $f->getOptions() === ['top' => 'Above content', 'bottom' => 'Below content'],
        );
    }

    protected function helperText(Field $field): string
    {
        $components = $field->getChildSchema(Field::BELOW_CONTENT_SCHEMA_KEY)?->getComponents() ?? [];

        return $components === [] ? '' : (string) $components[0]->getContent();
    }

    protected function fillCreateForm(array $overrides = []): Testable
    {
        return Livewire::test(CreateBlog::class)->fillForm($this->validFormData($overrides));
    }

    protected function validFormData(array $overrides = []): array
    {
        return [
            'title' => 'How To Make Gluten Free Bread',
            'slug' => 'how-to-make-gluten-free-bread',
            'short_title' => 'Gluten Free Bread',
            'description' => 'Everything you need to know about baking gluten free bread at home.',
            'tags' => ['baking'],
            'primary_tag_id' => null,
            'meta_tags' => 'bread,baking,gluten free',
            'meta_description' => 'How to make gluten free bread at home.',
            'body' => '<p>Start by weighing your flour.</p>',
            'show_author' => true,
            'status' => 'live',
            'faqs' => [],
            'faq_display' => null,
            'header' => [UploadedFile::fake()->image('header.jpg')],
            'social' => [UploadedFile::fake()->image('social.jpg')],
            ...$overrides,
        ];
    }
}
